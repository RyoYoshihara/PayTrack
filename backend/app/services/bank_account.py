import uuid

from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.bank_account import BankAccount


async def create_bank_account(
    db: AsyncSession, user_id: uuid.UUID, name: str, bank_name: str
) -> BankAccount:
    # Get max sort_order for this user
    result = await db.execute(
        select(BankAccount.sort_order)
        .where(BankAccount.user_id == user_id, BankAccount.is_deleted == False)
        .order_by(BankAccount.sort_order.desc())
        .limit(1)
    )
    max_order = result.scalar() or 0
    account = BankAccount(
        user_id=user_id,
        name=name,
        bank_name=bank_name,
        sort_order=max_order + 1,
    )
    db.add(account)
    await db.commit()
    await db.refresh(account)
    return account


async def get_bank_accounts(
    db: AsyncSession, user_id: uuid.UUID
) -> list[BankAccount]:
    result = await db.execute(
        select(BankAccount)
        .where(BankAccount.user_id == user_id, BankAccount.is_deleted == False)
        .order_by(BankAccount.sort_order)
    )
    return list(result.scalars().all())


async def get_bank_account(
    db: AsyncSession, account_id: uuid.UUID, user_id: uuid.UUID
) -> BankAccount | None:
    result = await db.execute(
        select(BankAccount).where(
            BankAccount.id == account_id,
            BankAccount.user_id == user_id,
            BankAccount.is_deleted == False,
        )
    )
    return result.scalar_one_or_none()


async def update_bank_account(
    db: AsyncSession, account: BankAccount, data: dict
) -> BankAccount:
    for key, value in data.items():
        setattr(account, key, value)
    await db.commit()
    await db.refresh(account)
    return account


async def delete_bank_account(db: AsyncSession, account: BankAccount) -> None:
    account.is_deleted = True
    await db.commit()


async def reorder_bank_accounts(
    db: AsyncSession, user_id: uuid.UUID, ids: list[uuid.UUID]
) -> None:
    for i, account_id in enumerate(ids):
        result = await db.execute(
            select(BankAccount).where(
                BankAccount.id == account_id,
                BankAccount.user_id == user_id,
            )
        )
        account = result.scalar_one_or_none()
        if account:
            account.sort_order = i
    await db.commit()
