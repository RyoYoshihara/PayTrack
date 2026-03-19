from pydantic import BaseModel


class StatusSummary(BaseModel):
    scheduled: int = 0
    completed: int = 0
    carried_over: int = 0
    cancelled: int = 0


class DashboardSummary(BaseModel):
    year: int
    month: int
    total_income: int
    total_expense: int
    balance: int
    status_summary: StatusSummary
