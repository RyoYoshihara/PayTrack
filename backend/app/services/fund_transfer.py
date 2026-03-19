import uuid
from datetime import date

from sqlalchemy import and_, extract, select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from app.models.fund_transfer import FundTransfer


async def create_fund_transfer(
    db: AsyncSession, user_id: uuid.UUID, data: dict
) -> FundTransfer:
    ft = FundTransfer(
        user_id=user_id,
        from_account_id=data["from_account_id"],
        to_account_id=data["to_account_id"],
        amount=data["amount"],
        scheduled_date=data["scheduled_date"],
        memo=data.get("memo"),
    )
    db.add(ft)
    await db.commit()
    await db.refresh(ft)
    return ft


async def get_fund_transfers(
    db: AsyncSession, user_id: uuid.UUID, year: int, month: int
) -> list[FundTransfer]:
    result = await db.execute(
        select(FundTransfer)
        .options(selectinload(FundTransfer.from_account), selectinload(FundTransfer.to_account))
        .where(
            FundTransfer.user_id == user_id,
            extract("year", FundTransfer.scheduled_date) == year,
            extract("month", FundTransfer.scheduled_date) == month,
        )
        .order_by(FundTransfer.scheduled_date)
    )
    return list(result.scalars().all())


async def get_fund_transfer(
    db: AsyncSession, ft_id: uuid.UUID, user_id: uuid.UUID
) -> FundTransfer | None:
    result = await db.execute(
        select(FundTransfer)
        .options(selectinload(FundTransfer.from_account), selectinload(FundTransfer.to_account))
        .where(FundTransfer.id == ft_id, FundTransfer.user_id == user_id)
    )
    return result.scalar_one_or_none()


async def confirm_fund_transfer(
    db: AsyncSession, ft: FundTransfer, side: str
) -> FundTransfer:
    if side == "from":
        ft.from_confirmed = True
    elif side == "to":
        ft.to_confirmed = True

    if ft.from_confirmed and ft.to_confirmed:
        ft.status = "completed"

    await db.commit()
    await db.refresh(ft)
    return ft


async def cancel_fund_transfer(
    db: AsyncSession, ft: FundTransfer
) -> FundTransfer:
    ft.status = "cancelled"
    await db.commit()
    await db.refresh(ft)
    return ft
