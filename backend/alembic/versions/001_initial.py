"""initial migration

Revision ID: 001
Revises:
Create Date: 2026-03-19
"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects.postgresql import UUID

revision: str = "001"
down_revision: Union[str, None] = None
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    op.execute('CREATE EXTENSION IF NOT EXISTS "pgcrypto"')

    op.create_table(
        "users",
        sa.Column("id", UUID(as_uuid=True), primary_key=True, server_default=sa.text("gen_random_uuid()")),
        sa.Column("email", sa.String(255), nullable=False, unique=True),
        sa.Column("password_hash", sa.String(255), nullable=False),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
    )

    op.create_table(
        "transaction_rules",
        sa.Column("id", UUID(as_uuid=True), primary_key=True, server_default=sa.text("gen_random_uuid()")),
        sa.Column("user_id", UUID(as_uuid=True), sa.ForeignKey("users.id", ondelete="CASCADE"), nullable=False),
        sa.Column("title", sa.String(255), nullable=False),
        sa.Column("amount", sa.Integer, nullable=False),
        sa.Column("type", sa.String(20), nullable=False),
        sa.Column("recurrence", sa.String(20), nullable=False),
        sa.Column("day_of_month", sa.Integer, nullable=True),
        sa.Column("start_month", sa.Date, nullable=True),
        sa.Column("end_month", sa.Date, nullable=True),
        sa.Column("memo", sa.Text, nullable=True),
        sa.Column("is_deleted", sa.Boolean, nullable=False, server_default=sa.text("false")),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.CheckConstraint("amount > 0", name="ck_rules_amount_positive"),
        sa.CheckConstraint("type IN ('income', 'expense')", name="ck_rules_type"),
        sa.CheckConstraint("recurrence IN ('once', 'monthly')", name="ck_rules_recurrence"),
        sa.CheckConstraint("day_of_month BETWEEN 1 AND 31", name="ck_rules_day_of_month"),
    )
    op.create_index("idx_transaction_rules_user_id", "transaction_rules", ["user_id"])
    op.create_index(
        "idx_transaction_rules_user_not_deleted",
        "transaction_rules",
        ["user_id"],
        postgresql_where=sa.text("is_deleted = false"),
    )

    op.create_table(
        "transactions",
        sa.Column("id", UUID(as_uuid=True), primary_key=True, server_default=sa.text("gen_random_uuid()")),
        sa.Column("user_id", UUID(as_uuid=True), sa.ForeignKey("users.id", ondelete="CASCADE"), nullable=False),
        sa.Column("rule_id", UUID(as_uuid=True), sa.ForeignKey("transaction_rules.id", ondelete="SET NULL"), nullable=True),
        sa.Column("title", sa.String(255), nullable=False),
        sa.Column("amount", sa.Integer, nullable=False),
        sa.Column("type", sa.String(20), nullable=False),
        sa.Column("scheduled_date", sa.Date, nullable=False),
        sa.Column("actual_date", sa.Date, nullable=True),
        sa.Column("status", sa.String(20), nullable=False, server_default=sa.text("'scheduled'")),
        sa.Column("carried_over_from", UUID(as_uuid=True), sa.ForeignKey("transactions.id", ondelete="SET NULL"), nullable=True),
        sa.Column("memo", sa.Text, nullable=True),
        sa.Column("is_deleted", sa.Boolean, nullable=False, server_default=sa.text("false")),
        sa.Column("created_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False, server_default=sa.func.now()),
        sa.CheckConstraint("amount > 0", name="ck_txn_amount_positive"),
        sa.CheckConstraint("type IN ('income', 'expense')", name="ck_txn_type"),
        sa.CheckConstraint("status IN ('scheduled', 'completed', 'carried_over', 'cancelled')", name="ck_txn_status"),
    )
    op.create_index("idx_transactions_user_id", "transactions", ["user_id"])
    op.create_index("idx_transactions_user_scheduled_date", "transactions", ["user_id", "scheduled_date"])
    op.create_index("idx_transactions_user_status", "transactions", ["user_id", "status"])
    op.create_index("idx_transactions_rule_scheduled", "transactions", ["rule_id", "scheduled_date"])
    op.create_index("idx_transactions_carried_over", "transactions", ["carried_over_from"])

    # updated_at trigger
    op.execute("""
        CREATE OR REPLACE FUNCTION update_updated_at()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
    """)
    for table in ["users", "transaction_rules", "transactions"]:
        op.execute(f"""
            CREATE TRIGGER trg_{table}_updated_at
                BEFORE UPDATE ON {table} FOR EACH ROW EXECUTE FUNCTION update_updated_at();
        """)


def downgrade() -> None:
    for table in ["transactions", "transaction_rules", "users"]:
        op.execute(f"DROP TRIGGER IF EXISTS trg_{table}_updated_at ON {table}")
    op.execute("DROP FUNCTION IF EXISTS update_updated_at()")
    op.drop_table("transactions")
    op.drop_table("transaction_rules")
    op.drop_table("users")
