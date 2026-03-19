import uuid

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.fund_transfer import (
    FundTransferConfirm,
    FundTransferCreate,
    FundTransferResponse,
)
from app.services.fund_transfer import (
    cancel_fund_transfer,
    confirm_fund_transfer,
    create_fund_transfer,
    get_fund_transfer,
    get_fund_transfers,
)

router = APIRouter(prefix="/api/v1/fund-transfers", tags=["fund-transfers"])


def _to_response(ft) -> FundTransferResponse:
    data = FundTransferResponse.model_validate(ft)
    if ft.from_account:
        data.from_account_name = f"{ft.from_account.name}（{ft.from_account.bank_name}）"
    if ft.to_account:
        data.to_account_name = f"{ft.to_account.name}（{ft.to_account.bank_name}）"
    return data


@router.get("")
async def list_transfers(
    year: int = Query(...),
    month: int = Query(..., ge=1, le=12),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    transfers = await get_fund_transfers(db, user.id, year, month)
    return {"data": [_to_response(ft) for ft in transfers]}


@router.post("", status_code=status.HTTP_201_CREATED)
async def create(
    body: FundTransferCreate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    if body.from_account_id == body.to_account_id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": "移動元と移動先は異なる口座を指定してください"},
        )
    ft = await create_fund_transfer(db, user.id, body.model_dump())
    ft_loaded = await get_fund_transfer(db, ft.id, user.id)
    return {"data": _to_response(ft_loaded)}


@router.patch("/{transfer_id}/confirm")
async def confirm(
    transfer_id: uuid.UUID,
    body: FundTransferConfirm,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    ft = await get_fund_transfer(db, transfer_id, user.id)
    if not ft:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "資金移動が見つかりません"},
        )
    if ft.status != "scheduled":
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": "確認できるのは予定状態の資金移動のみです"},
        )
    updated = await confirm_fund_transfer(db, ft, body.side)
    ft_loaded = await get_fund_transfer(db, updated.id, user.id)
    return {"data": _to_response(ft_loaded)}


@router.patch("/{transfer_id}/cancel")
async def cancel(
    transfer_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    ft = await get_fund_transfer(db, transfer_id, user.id)
    if not ft:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "資金移動が見つかりません"},
        )
    if ft.status != "scheduled":
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": "キャンセルできるのは予定状態の資金移動のみです"},
        )
    updated = await cancel_fund_transfer(db, ft)
    ft_loaded = await get_fund_transfer(db, updated.id, user.id)
    return {"data": _to_response(ft_loaded)}
