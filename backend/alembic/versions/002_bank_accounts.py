"""add bank_accounts and fund_transfers

Revision ID: 002
Revises: 001
Create Date: 2026-03-19
"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects.postgresql import UUID

revision: str = "002"
down_revision: Union[str, None] = "001"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    # bank_accounts
    op.create_table(
        "bank_accounts",
        sa.Column("id", UUID(as_uuid=True), primary_key=True, server_default=sa.text("gen_random_uuid()")),
        sa.Column("user_id", UUID(as_uuid=True), sa.ForeignKey("users.id", ondelete="CASCADE"), nullable=False),
        sa.Column("name", sa.String(255), nullable=False),
        sa.Column("bank_name", sa.String(255), nullable=False),
        sa.Column("sort_order", sa.Integer, nullable=False, server_default=sa.text("0")),
        sa.Column("is_deleted", sa.Boolean, nullable=False, server_default=sa.text("false")),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
    )
    op.create_index("idx_bank_accounts_user_id", "bank_accounts", ["user_id"])

    # Add bank_account_id to existing tables (nullable for backward compat)
    op.add_column(
        "transactions",
        sa.Column("bank_account_id", UUID(as_uuid=True), sa.ForeignKey("bank_accounts.id", ondelete="SET NULL"), nullable=True),
    )
    op.create_index("idx_transactions_bank_account", "transactions", ["bank_account_id"])

    op.add_column(
        "transaction_rules",
        sa.Column("bank_account_id", UUID(as_uuid=True), sa.ForeignKey("bank_accounts.id", ondelete="SET NULL"), nullable=True),
    )

    # fund_transfers
    op.create_table(
        "fund_transfers",
        sa.Column("id", UUID(as_uuid=True), primary_key=True, server_default=sa.text("gen_random_uuid()")),
        sa.Column("user_id", UUID(as_uuid=True), sa.ForeignKey("users.id", ondelete="CASCADE"), nullable=False),
        sa.Column("from_account_id", UUID(as_uuid=True), sa.ForeignKey("bank_accounts.id", ondelete="CASCADE"), nullable=False),
        sa.Column("to_account_id", UUID(as_uuid=True), sa.ForeignKey("bank_accounts.id", ondelete="CASCADE"), nullable=False),
        sa.Column("amount", sa.Integer, nullable=False),
        sa.Column("scheduled_date", sa.Date, nullable=False),
        sa.Column("memo", sa.Text, nullable=True),
        sa.Column("from_confirmed", sa.Boolean, nullable=False, server_default=sa.text("false")),
        sa.Column("to_confirmed", sa.Boolean, nullable=False, server_default=sa.text("false")),
        sa.Column("status", sa.String(20), nullable=False, server_default=sa.text("'scheduled'")),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.CheckConstraint("amount > 0", name="ck_ft_amount_positive"),
        sa.CheckConstraint("status IN ('scheduled', 'completed', 'cancelled')", name="ck_ft_status"),
    )
    op.create_index("idx_fund_transfers_user_id", "fund_transfers", ["user_id"])
    op.create_index("idx_fund_transfers_scheduled_date", "fund_transfers", ["user_id", "scheduled_date"])

    # Triggers for new tables
    for table in ["bank_accounts", "fund_transfers"]:
        op.execute(f"""
            CREATE TRIGGER trg_{table}_updated_at
                BEFORE UPDATE ON {table} FOR EACH ROW EXECUTE FUNCTION update_updated_at();
        """)


def downgrade() -> None:
    for table in ["fund_transfers", "bank_accounts"]:
        op.execute(f"DROP TRIGGER IF EXISTS trg_{table}_updated_at ON {table}")
    op.drop_column("transaction_rules", "bank_account_id")
    op.drop_index("idx_transactions_bank_account", "transactions")
    op.drop_column("transactions", "bank_account_id")
    op.drop_table("fund_transfers")
    op.drop_table("bank_accounts")
