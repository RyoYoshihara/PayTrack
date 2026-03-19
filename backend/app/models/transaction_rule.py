import uuid
from datetime import date, datetime, timezone

from sqlalchemy import Boolean, CheckConstraint, Date, DateTime, ForeignKey, Integer, String, Text
from sqlalchemy.dialects.postgresql import UUID
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.models.user import Base


class TransactionRule(Base):
    __tablename__ = "transaction_rules"
    __table_args__ = (
        CheckConstraint("amount > 0", name="ck_rules_amount_positive"),
        CheckConstraint("type IN ('income', 'expense')", name="ck_rules_type"),
        CheckConstraint(
            "recurrence IN ('once', 'monthly')", name="ck_rules_recurrence"
        ),
        CheckConstraint(
            "day_of_month BETWEEN 1 AND 31", name="ck_rules_day_of_month"
        ),
    )

    id: Mapped[uuid.UUID] = mapped_column(
        UUID(as_uuid=True), primary_key=True, default=uuid.uuid4
    )
    user_id: Mapped[uuid.UUID] = mapped_column(
        UUID(as_uuid=True), ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    amount: Mapped[int] = mapped_column(Integer, nullable=False)
    type: Mapped[str] = mapped_column(String(20), nullable=False)
    recurrence: Mapped[str] = mapped_column(String(20), nullable=False)
    day_of_month: Mapped[int | None] = mapped_column(Integer, nullable=True)
    start_month: Mapped[date | None] = mapped_column(Date, nullable=True)
    end_month: Mapped[date | None] = mapped_column(Date, nullable=True)
    bank_account_id: Mapped[uuid.UUID | None] = mapped_column(
        UUID(as_uuid=True),
        ForeignKey("bank_accounts.id", ondelete="SET NULL"),
        nullable=True,
    )
    memo: Mapped[str | None] = mapped_column(Text, nullable=True)
    is_deleted: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True),
        default=lambda: datetime.now(timezone.utc),
    )
    updated_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True),
        default=lambda: datetime.now(timezone.utc),
        onupdate=lambda: datetime.now(timezone.utc),
    )

    user = relationship("User", back_populates="rules")
    transactions = relationship("Transaction", back_populates="rule")
    bank_account = relationship("BankAccount")
