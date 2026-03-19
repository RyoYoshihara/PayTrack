import calendar
import uuid
from datetime import date

from sqlalchemy import and_, extract, select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.transaction import Transaction
from app.models.transaction_rule import TransactionRule


def adjust_day(year: int, month: int, day: int) -> int:
    max_day = calendar.monthrange(year, month)[1]
    return min(day, max_day)


async def generate_monthly(
    db: AsyncSession, user_id: uuid.UUID, target_year: int, target_month: int
) -> dict:
    rules = await db.execute(
        select(TransactionRule).where(
            TransactionRule.user_id == user_id,
            TransactionRule.recurrence == "monthly",
            TransactionRule.is_deleted == False,
        )
    )
    rules = list(rules.scalars().all())

    generated = 0
    skipped = 0
    target_first = date(target_year, target_month, 1)

    for rule in rules:
        # Check date range
        if rule.start_month and target_first < rule.start_month:
            skipped += 1
            continue
        if rule.end_month and target_first > rule.end_month:
            skipped += 1
            continue

        # Check duplicate
        existing = await db.execute(
            select(Transaction.id).where(
                Transaction.rule_id == rule.id,
                extract("year", Transaction.scheduled_date) == target_year,
                extract("month", Transaction.scheduled_date) == target_month,
            )
        )
        if existing.scalar_one_or_none() is not None:
            skipped += 1
            continue

        day = adjust_day(target_year, target_month, rule.day_of_month)
        txn = Transaction(
            user_id=user_id,
            rule_id=rule.id,
            bank_account_id=rule.bank_account_id,
            title=rule.title,
            amount=rule.amount,
            type=rule.type,
            scheduled_date=date(target_year, target_month, day),
            memo=rule.memo,
        )
        db.add(txn)
        generated += 1

    await db.commit()
    return {"generated_count": generated, "skipped_count": skipped}


async def carry_over(
    db: AsyncSession, user_id: uuid.UUID, source_year: int, source_month: int
) -> int:
    max_day = calendar.monthrange(source_year, source_month)[1]
    end_of_month = date(source_year, source_month, max_day)

    result = await db.execute(
        select(Transaction).where(
            Transaction.user_id == user_id,
            Transaction.status == "scheduled",
            Transaction.is_deleted == False,
            Transaction.scheduled_date <= end_of_month,
            extract("year", Transaction.scheduled_date) == source_year,
            extract("month", Transaction.scheduled_date) == source_month,
        )
    )
    transactions = list(result.scalars().all())

    # Calculate next month
    if source_month == 12:
        next_year, next_month = source_year + 1, 1
    else:
        next_year, next_month = source_year, source_month + 1

    count = 0
    for txn in transactions:
        txn.status = "carried_over"

        day = adjust_day(next_year, next_month, txn.scheduled_date.day)
        new_txn = Transaction(
            user_id=user_id,
            rule_id=txn.rule_id,
            bank_account_id=txn.bank_account_id,
            title=txn.title,
            amount=txn.amount,
            type=txn.type,
            scheduled_date=date(next_year, next_month, day),
            carried_over_from=txn.id,
            memo=txn.memo,
        )
        db.add(new_txn)
        count += 1

    await db.commit()
    return count
