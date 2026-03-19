import uuid
from datetime import date, datetime
from typing import Literal

from pydantic import BaseModel, field_validator


class FundTransferCreate(BaseModel):
    from_account_id: uuid.UUID
    to_account_id: uuid.UUID
    amount: int
    scheduled_date: date
    memo: str | None = None

    @field_validator("amount")
    @classmethod
    def validate_amount(cls, v: int) -> int:
        if v < 1:
            raise ValueError("金額は1以上で入力してください")
        return v


class FundTransferConfirm(BaseModel):
    side: Literal["from", "to"]


class FundTransferResponse(BaseModel):
    id: uuid.UUID
    from_account_id: uuid.UUID
    to_account_id: uuid.UUID
    from_account_name: str | None = None
    to_account_name: str | None = None
    amount: int
    scheduled_date: date
    memo: str | None
    from_confirmed: bool
    to_confirmed: bool
    status: str
    created_at: datetime
    updated_at: datetime

    model_config = {"from_attributes": True}
