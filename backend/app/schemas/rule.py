import uuid
from datetime import datetime
from typing import Literal

from pydantic import BaseModel, field_validator, model_validator


class RuleCreate(BaseModel):
    title: str
    amount: int
    type: Literal["income", "expense"]
    recurrence: Literal["once", "monthly"]
    day_of_month: int | None = None
    start_month: str | None = None
    end_month: str | None = None
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

    @field_validator("day_of_month")
    @classmethod
    def validate_day(cls, v: int | None) -> int | None:
        if v is not None and (v < 1 or v > 31):
            raise ValueError("日は1〜31で入力してください")
        return v

    @model_validator(mode="after")
    def validate_monthly_fields(self):
        if self.recurrence == "monthly" and self.day_of_month is None:
            raise ValueError("毎月の場合、日の指定は必須です")
        return self


class RuleUpdate(BaseModel):
    title: str | None = None
    amount: int | None = None
    type: Literal["income", "expense"] | None = None
    recurrence: Literal["once", "monthly"] | None = None
    day_of_month: int | None = None
    start_month: str | None = None
    end_month: str | None = None
    bank_account_id: uuid.UUID | None = None
    memo: str | None = None


class RuleResponse(BaseModel):
    id: uuid.UUID
    user_id: uuid.UUID
    bank_account_id: uuid.UUID | None
    title: str
    amount: int
    type: str
    recurrence: str
    day_of_month: int | None
    start_month: str | None
    end_month: str | None
    memo: str | None
    created_at: datetime
    updated_at: datetime

    model_config = {"from_attributes": True}

    @field_validator("start_month", "end_month", mode="before")
    @classmethod
    def format_month(cls, v):
        if v is None:
            return None
        if hasattr(v, "strftime"):
            return v.strftime("%Y-%m")
        return v
