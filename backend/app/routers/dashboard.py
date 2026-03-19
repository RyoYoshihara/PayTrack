from fastapi import APIRouter, Depends, Query
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.dashboard import DashboardSummary, StatusSummary
from app.services.transaction import get_dashboard_summary, get_dashboard_summary_by_account

router = APIRouter(prefix="/api/v1/dashboard", tags=["dashboard"])


@router.get("/summary")
async def summary(
    year: int = Query(...),
    month: int = Query(..., ge=1, le=12),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    data = await get_dashboard_summary(db, user.id, year, month)
    return {
        "data": DashboardSummary(
            year=data["year"],
            month=data["month"],
            total_income=data["total_income"],
            total_expense=data["total_expense"],
            balance=data["balance"],
            status_summary=StatusSummary(**data["status_summary"]),
        )
    }


@router.get("/summary-by-account")
async def summary_by_account(
    year: int = Query(...),
    month: int = Query(..., ge=1, le=12),
    user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    data = await get_dashboard_summary_by_account(db, user.id, year, month)
    return {"data": data}
