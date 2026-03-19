import calendar
import uuid
from datetime import date

from sqlalchemy import and_, extract, func, select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.bank_account import BankAccount
from app.models.fund_transfer import FundTransfer
from app.models.transaction import Transaction

VALID_TRANSITIONS = {
    "scheduled": {"completed", "carried_over", "cancelled"},
    "carried_over": {"completed", "cancelled"},
}


async def create_transaction(
    db: AsyncSession, user_id: uuid.UUID, data: dict
) -> Transaction:
    txn = Transaction(
        user_id=user_id,
        title=data["title"],
        amount=data["amount"],
        type=data["type"],
        scheduled_date=data["scheduled_date"],
        memo=data.get("memo"),
    )
    db.add(txn)
    await db.commit()
    await db.refresh(txn)
    return txn


async def get_transactions(
    db: AsyncSession,
    user_id: uuid.UUID,
    year: int,
    month: int,
    page: int = 1,
    per_page: int = 20,
    type_filter: str | None = None,
    status_filter: str | None = None,
) -> tuple[list[Transaction], int]:
    base_cond = [
        Transaction.user_id == user_id,
        Transaction.is_deleted == False,
        extract("year", Transaction.scheduled_date) == year,
        extract("month", Transaction.scheduled_date) == month,
    ]
    if type_filter:
        base_cond.append(Transaction.type == type_filter)
    if status_filter:
        base_cond.append(Transaction.status == status_filter)

    count_query = select(func.count()).select_from(Transaction).where(and_(*base_cond))
    total = (await db.execute(count_query)).scalar() or 0

    query = (
        select(Transaction)
        .where(and_(*base_cond))
        .order_by(Transaction.scheduled_date)
        .offset((page - 1) * per_page)
        .limit(per_page)
    )
    result = await db.execute(query)
    return list(result.scalars().all()), total


async def get_transaction(
    db: AsyncSession, txn_id: uuid.UUID, user_id: uuid.UUID
) -> Transaction | None:
    result = await db.execute(
        select(Transaction).where(
            Transaction.id == txn_id,
            Transaction.user_id == user_id,
            Transaction.is_deleted == False,
        )
    )
    return result.scalar_one_or_none()


async def update_transaction(
    db: AsyncSession, txn: Transaction, data: dict
) -> Transaction:
    for key, value in data.items():
        setattr(txn, key, value)
    await db.commit()
    await db.refresh(txn)
    return txn


async def update_status(
    db: AsyncSession, txn: Transaction, new_status: str, actual_date: date | None
) -> Transaction:
    txn.status = new_status
    if new_status == "completed" and actual_date:
        txn.actual_date = actual_date
    elif new_status == "carried_over":
        # Generate a new scheduled transaction in the next month
        src_year = txn.scheduled_date.year
        src_month = txn.scheduled_date.month
        if src_month == 12:
            next_year, next_month = src_year + 1, 1
        else:
            next_year, next_month = src_year, src_month + 1
        max_day = calendar.monthrange(next_year, next_month)[1]
        day = min(txn.scheduled_date.day, max_day)
        new_txn = Transaction(
            user_id=txn.user_id,
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
    await db.commit()
    await db.refresh(txn)
    return txn


async def delete_transaction(db: AsyncSession, txn: Transaction) -> None:
    txn.is_deleted = True
    await db.commit()


async def get_dashboard_summary(
    db: AsyncSession, user_id: uuid.UUID, year: int, month: int
) -> dict:
    base_cond = [
        Transaction.user_id == user_id,
        Transaction.is_deleted == False,
        extract("year", Transaction.scheduled_date) == year,
        extract("month", Transaction.scheduled_date) == month,
    ]

    # Income/expense totals (completed only)
    income_q = select(func.coalesce(func.sum(Transaction.amount), 0)).where(
        and_(*base_cond, Transaction.type == "income", Transaction.status == "completed")
    )
    expense_q = select(func.coalesce(func.sum(Transaction.amount), 0)).where(
        and_(*base_cond, Transaction.type == "expense", Transaction.status == "completed")
    )

    total_income = (await db.execute(income_q)).scalar() or 0
    total_expense = (await db.execute(expense_q)).scalar() or 0

    # Status counts
    status_q = (
        select(Transaction.status, func.count())
        .where(and_(*base_cond))
        .group_by(Transaction.status)
    )
    status_result = await db.execute(status_q)
    status_counts = {row[0]: row[1] for row in status_result.all()}

    return {
        "year": year,
        "month": month,
        "total_income": total_income,
        "total_expense": total_expense,
        "balance": total_income - total_expense,
        "status_summary": {
            "scheduled": status_counts.get("scheduled", 0),
            "completed": status_counts.get("completed", 0),
            "carried_over": status_counts.get("carried_over", 0),
            "cancelled": status_counts.get("cancelled", 0),
        },
    }


async def get_dashboard_summary_by_account(
    db: AsyncSession, user_id: uuid.UUID, year: int, month: int
) -> list[dict]:
    # Get all active bank accounts
    accounts_result = await db.execute(
        select(BankAccount)
        .where(BankAccount.user_id == user_id, BankAccount.is_deleted == False)
        .order_by(BankAccount.sort_order)
    )
    accounts = list(accounts_result.scalars().all())

    base_cond = [
        Transaction.user_id == user_id,
        Transaction.is_deleted == False,
        extract("year", Transaction.scheduled_date) == year,
        extract("month", Transaction.scheduled_date) == month,
        Transaction.status == "completed",
    ]

    # Fund transfer conditions
    ft_base = [
        FundTransfer.user_id == user_id,
        extract("year", FundTransfer.scheduled_date) == year,
        extract("month", FundTransfer.scheduled_date) == month,
        FundTransfer.status == "completed",
    ]

    result = []
    for account in accounts:
        # Transaction income/expense for this account
        income = (await db.execute(
            select(func.coalesce(func.sum(Transaction.amount), 0)).where(
                and_(*base_cond, Transaction.bank_account_id == account.id, Transaction.type == "income")
            )
        )).scalar() or 0

        expense = (await db.execute(
            select(func.coalesce(func.sum(Transaction.amount), 0)).where(
                and_(*base_cond, Transaction.bank_account_id == account.id, Transaction.type == "expense")
            )
        )).scalar() or 0

        # Fund transfers: outgoing (from this account)
        transfer_out = (await db.execute(
            select(func.coalesce(func.sum(FundTransfer.amount), 0)).where(
                and_(*ft_base, FundTransfer.from_account_id == account.id)
            )
        )).scalar() or 0

        # Fund transfers: incoming (to this account)
        transfer_in = (await db.execute(
            select(func.coalesce(func.sum(FundTransfer.amount), 0)).where(
                and_(*ft_base, FundTransfer.to_account_id == account.id)
            )
        )).scalar() or 0

        result.append({
            "account_id": str(account.id),
            "account_name": f"{account.name}（{account.bank_name}）",
            "total_income": income + transfer_in,
            "total_expense": expense + transfer_out,
            "balance": (income + transfer_in) - (expense + transfer_out),
        })

    return result
