from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.services.batch import carry_over, generate_monthly

router = APIRouter(prefix="/api/v1/batch", tags=["batch"])


class GenerateRequest(BaseModel):
    target_month: str


class CarryOverRequest(BaseModel):
    source_month: str


def parse_ym(ym: str) -> tuple[int, int]:
    try:
        parts = ym.split("-")
        if len(parts) != 2:
            raise ValueError()
        year, month = int(parts[0]), int(parts[1])
        if month < 1 or month > 12:
            raise ValueError()
        return year, month
    except (ValueError, IndexError):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail={"code": "VALIDATION_ERROR", "message": "YYYY-MM形式で指定してください"},
        )


@router.post("/generate")
async def batch_generate(
    body: GenerateRequest,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    year, month = parse_ym(body.target_month)
    result = await generate_monthly(db, user.id, year, month)
    return {
        "data": {
            "target_month": body.target_month,
            **result,
        }
    }


@router.post("/carry-over")
async def batch_carry_over(
    body: CarryOverRequest,
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    year, month = parse_ym(body.source_month)
    count = await carry_over(db, user.id, year, month)
    return {
        "data": {
            "source_month": body.source_month,
            "carried_over_count": count,
        }
    }
