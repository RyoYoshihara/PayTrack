import uuid

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.rule import RuleCreate, RuleResponse, RuleUpdate
from app.services.rule import create_rule, delete_rule, get_rule, get_rules, update_rule

router = APIRouter(prefix="/api/v1/rules", tags=["rules"])


@router.post("", status_code=status.HTTP_201_CREATED)
async def create(
    body: RuleCreate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    try:
        rule = await create_rule(db, user.id, body.model_dump())
    except ValueError as e:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": str(e)},
        )
    return {"data": RuleResponse.model_validate(rule)}


@router.get("")
async def list_rules(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    type: str | None = None,
    recurrence: str | None = None,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    rules, total = await get_rules(db, user.id, page, per_page, type, recurrence)
    return {
        "data": [RuleResponse.model_validate(r) for r in rules],
        "total": total,
        "page": page,
        "per_page": per_page,
    }


@router.get("/{rule_id}")
async def detail(
    rule_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    rule = await get_rule(db, rule_id, user.id)
    if not rule:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "ルールが見つかりません"},
        )
    return {"data": RuleResponse.model_validate(rule)}


@router.put("/{rule_id}")
async def update(
    rule_id: uuid.UUID,
    body: RuleUpdate,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    rule = await get_rule(db, rule_id, user.id)
    if not rule:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "ルールが見つかりません"},
        )
    try:
        updated = await update_rule(db, rule, body.model_dump(exclude_unset=True))
    except ValueError as e:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": str(e)},
        )
    return {"data": RuleResponse.model_validate(updated)}


@router.delete("/{rule_id}")
async def delete(
    rule_id: uuid.UUID,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    rule = await get_rule(db, rule_id, user.id)
    if not rule:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail={"code": "NOT_FOUND", "message": "ルールが見つかりません"},
        )
    await delete_rule(db, rule)
    return {"data": {"message": "ルールを削除しました"}}
