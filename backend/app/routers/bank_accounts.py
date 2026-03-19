import uuid

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.bank_account import (
    BankAccountCreate,
    BankAccountResponse,
    BankAccountUpdate,
    ReorderRequest,
)
from app.services.bank_account import (
    create_bank_account,
    delete_bank_account,
    get_bank_account,
    get_bank_accounts,
    reorder_bank_accounts,
    update_bank_account,
)

router = APIRouter(prefix="/api/v1/bank-accounts", tags=["bank-accounts"])


@router.get("")
async def list_accounts(
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    accounts = await get_bank_accounts(db, user.id)
    return {"data": [BankAccountResponse.model_validate(a) for a in accounts]}


@router.post("", status_code=status.HTTP_201_CREATED)
async def create(
    body: BankAccountCreate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    account = await create_bank_account(db, user.id, body.name, body.bank_name)
    return {"data": BankAccountResponse.model_validate(account)}


@router.put("/reorder")
async def reorder(
    body: ReorderRequest,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    await reorder_bank_accounts(db, user.id, body.ids)
    accounts = await get_bank_accounts(db, user.id)
    return {"data": [BankAccountResponse.model_validate(a) for a in accounts]}


@router.put("/{account_id}")
async def update(
    account_id: uuid.UUID,
    body: BankAccountUpdate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    account = await get_bank_account(db, account_id, user.id)
    if not account:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "口座が見つかりません"},
        )
    updated = await update_bank_account(db, account, body.model_dump(exclude_unset=True))
    return {"data": BankAccountResponse.model_validate(updated)}


@router.delete("/{account_id}")
async def delete(
    account_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    account = await get_bank_account(db, account_id, user.id)
    if not account:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "口座が見つかりません"},
        )
    await delete_bank_account(db, account)
    return {"data": {"message": "口座を削除しました"}}
