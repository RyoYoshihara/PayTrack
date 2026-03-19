import uuid
from datetime import date, datetime
from typing import Literal

from pydantic import BaseModel, field_validator


class TransactionCreate(BaseModel):
    title: str
    amount: int
    type: Literal["income", "expense"]
    scheduled_date: date
    bank_account_id: uuid.UUID
    memo: str | None = None

    @field_validator("title")
    @classmethod
    def validate_title(cls, v: str) -> str:
        if not v or len(v) > 255:
            raise ValueError("件名は1〜255文字で入力してください")
        return v

    @field_validator("amount")
    @classmethod
    def validate_amount(cls, v: int) -> int:
        if v < 1:
            raise ValueError("金額は1以上で入力してください")
        return v


class TransactionUpdate(BaseModel):
    title: str | None = None
    amount: int | None = None
    scheduled_date: date | None = None
    bank_account_id: uuid.UUID | None = None
    memo: str | None = None


class StatusUpdate(BaseModel):
    status: Literal["completed", "carried_over", "cancelled"]
    actual_date: date | None = None


class TransactionResponse(BaseModel):
    id: uuid.UUID
    rule_id: uuid.UUID | None
    bank_account_id: uuid.UUID | None
    title: str
    amount: int
    type: str
    scheduled_date: date
    actual_date: date | None
    status: str
    carried_over_from: uuid.UUID | None
    memo: str | None
    created_at: datetime
    updated_at: datetime

    model_config = {"from_attributes": True}
