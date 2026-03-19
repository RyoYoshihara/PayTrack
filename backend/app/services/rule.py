import calendar
import uuid
from datetime import date

from sqlalchemy import func, select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.transaction import Transaction
from app.models.transaction_rule import TransactionRule


def parse_month(month_str: str | None) -> date | None:
    if month_str is None:
        return None
    try:
        parts = month_str.split("-")
        return date(int(parts[0]), int(parts[1]), 1)
    except (IndexError, ValueError) as e:
        raise ValueError(f"無効な月形式です: {month_str}") from e


async def create_rule(
    db: AsyncSession, user_id: uuid.UUID, data: dict
) -> TransactionRule:
    rule = TransactionRule(
        user_id=user_id,
        title=data["title"],
        amount=data["amount"],
        type=data["type"],
        recurrence=data["recurrence"],
        day_of_month=data.get("day_of_month"),
        start_month=parse_month(data.get("start_month")),
        end_month=parse_month(data.get("end_month")),
        bank_account_id=data.get("bank_account_id"),
        memo=data.get("memo"),
    )
    db.add(rule)
    await db.flush()  # Get rule.id before creating transactions

    today = date.today()
    current_month_first = date(today.year, today.month, 1)

    if rule.recurrence == "monthly":
        # Generate transaction for current month if within range
        if rule.start_month is None or rule.start_month <= current_month_first:
            if rule.end_month is None or rule.end_month >= current_month_first:
                max_day = calendar.monthrange(today.year, today.month)[1]
                day = min(rule.day_of_month, max_day)
                txn = Transaction(
                    user_id=user_id,
                    rule_id=rule.id,
                    bank_account_id=rule.bank_account_id,
                    title=rule.title,
                    amount=rule.amount,
                    type=rule.type,
                    scheduled_date=date(today.year, today.month, day),
                    memo=rule.memo,
                )
                db.add(txn)
    elif rule.recurrence == "once":
        # Generate a single transaction using start_month + day_of_month or current date
        if rule.start_month and rule.day_of_month:
            max_day = calendar.monthrange(rule.start_month.year, rule.start_month.month)[1]
            day = min(rule.day_of_month, max_day)
            scheduled = date(rule.start_month.year, rule.start_month.month, day)
        elif rule.start_month:
            scheduled = rule.start_month
        else:
            scheduled = today
        txn = Transaction(
            user_id=user_id,
            rule_id=rule.id,
            bank_account_id=rule.bank_account_id,
            title=rule.title,
            amount=rule.amount,
            type=rule.type,
            scheduled_date=scheduled,
            memo=rule.memo,
        )
        db.add(txn)

    await db.commit()
    await db.refresh(rule)
    return rule


async def get_rules(
    db: AsyncSession,
    user_id: uuid.UUID,
    page: int = 1,
    per_page: int = 20,
    type_filter: str | None = None,
    recurrence_filter: str | None = None,
) -> tuple[list[TransactionRule], int]:
    query = select(TransactionRule).where(
        TransactionRule.user_id == user_id,
        TransactionRule.is_deleted == False,
    )
    count_query = select(func.count()).select_from(TransactionRule).where(
        TransactionRule.user_id == user_id,
        TransactionRule.is_deleted == False,
    )

    if type_filter:
        query = query.where(TransactionRule.type == type_filter)
        count_query = count_query.where(TransactionRule.type == type_filter)
    if recurrence_filter:
        query = query.where(TransactionRule.recurrence == recurrence_filter)
        count_query = count_query.where(TransactionRule.recurrence == recurrence_filter)

    total = (await db.execute(count_query)).scalar() or 0
    query = query.order_by(TransactionRule.created_at.desc())
    query = query.offset((page - 1) * per_page).limit(per_page)
    result = await db.execute(query)
    return list(result.scalars().all()), total


async def get_rule(
    db: AsyncSession, rule_id: uuid.UUID, user_id: uuid.UUID
) -> TransactionRule | None:
    result = await db.execute(
        select(TransactionRule).where(
            TransactionRule.id == rule_id,
            TransactionRule.user_id == user_id,
            TransactionRule.is_deleted == False,
        )
    )
    return result.scalar_one_or_none()


async def update_rule(
    db: AsyncSession, rule: TransactionRule, data: dict
) -> TransactionRule:
    for key, value in data.items():
        if key in ("start_month", "end_month"):
            value = parse_month(value)
        setattr(rule, key, value)
    await db.commit()
    await db.refresh(rule)
    return rule


async def delete_rule(db: AsyncSession, rule: TransactionRule) -> None:
    rule.is_deleted = True
    await db.commit()
