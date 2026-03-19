import uuid
from datetime import datetime

from pydantic import BaseModel, field_validator


class BankAccountCreate(BaseModel):
    name: str
    bank_name: str

    @field_validator("name", "bank_name")
    @classmethod
    def validate_not_empty(cls, v: str) -> str:
        if not v or len(v) > 255:
            raise ValueError("1〜255文字で入力してください")
        return v


class BankAccountUpdate(BaseModel):
    name: str | None = None
    bank_name: str | None = None


class BankAccountResponse(BaseModel):
    id: uuid.UUID
    name: str
    bank_name: str
    sort_order: int
    created_at: datetime
    updated_at: datetime

    model_config = {"from_attributes": True}


class ReorderRequest(BaseModel):
    ids: list[uuid.UUID]
