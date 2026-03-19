import uuid

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.transaction import (
    StatusUpdate,
    TransactionCreate,
    TransactionResponse,
    TransactionUpdate,
)
from app.services.transaction import (
    VALID_TRANSITIONS,
    create_transaction,
    delete_transaction,
    get_transaction,
    get_transactions,
    update_status,
    update_transaction,
)

router = APIRouter(prefix="/api/v1/transactions", tags=["transactions"])


@router.post("", status_code=status.HTTP_201_CREATED)
async def create(
    body: TransactionCreate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txn = await create_transaction(db, user.id, body.model_dump())
    return {"data": TransactionResponse.model_validate(txn)}


@router.get("")
async def list_transactions(
    year: int = Query(...),
    month: int = Query(..., ge=1, le=12),
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    type: str | None = None,
    status_filter: str | None = Query(None, alias="status"),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txns, total = await get_transactions(
        db, user.id, year, month, page, per_page, type, status_filter
    )
    return {
        "data": [TransactionResponse.model_validate(t) for t in txns],
        "total": total,
        "page": page,
        "per_page": per_page,
    }


@router.get("/{transaction_id}")
async def detail(
    transaction_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txn = await get_transaction(db, transaction_id, user.id)
    if not txn:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "トランザクションが見つかりません"},
        )
    return {"data": TransactionResponse.model_validate(txn)}


@router.put("/{transaction_id}")
async def update(
    transaction_id: uuid.UUID,
    body: TransactionUpdate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txn = await get_transaction(db, transaction_id, user.id)
    if not txn:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "トランザクションが見つかりません"},
        )
    if txn.status in ("completed", "cancelled"):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={
                "code": "VALIDATION_ERROR",
                "message": "完了・キャンセル済みのトランザクションは更新できません",
            },
        )
    updated = await update_transaction(db, txn, body.model_dump(exclude_unset=True))
    return {"data": TransactionResponse.model_validate(updated)}


@router.patch("/{transaction_id}/status")
async def change_status(
    transaction_id: uuid.UUID,
    body: StatusUpdate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txn = await get_transaction(db, transaction_id, user.id)
    if not txn:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "トランザクションが見つかりません"},
        )

    allowed = VALID_TRANSITIONS.get(txn.status, set())
    if body.status not in allowed:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={
                "code": "VALIDATION_ERROR",
                "message": f"ステータスを {txn.status} から {body.status} に変更できません",
            },
        )

    if body.status == "completed" and not body.actual_date:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={
                "code": "VALIDATION_ERROR",
                "message": "完了時は実施日を指定してください",
            },
        )

    updated = await update_status(db, txn, body.status, body.actual_date)
    return {"data": TransactionResponse.model_validate(updated)}


@router.delete("/{transaction_id}")
async def delete(
    transaction_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    txn = await get_transaction(db, transaction_id, user.id)
    if not txn:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "トランザクションが見つかりません"},
        )
    await delete_transaction(db, txn)
    return {"data": {"message": "トランザクションを削除しました"}}
