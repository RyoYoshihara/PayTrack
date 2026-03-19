import subprocess
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import settings
from app.routers import auth, bank_accounts, batch, dashboard, fund_transfers, rules, transactions


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Run migrations on startup via subprocess to avoid event loop conflict
    subprocess.run(
        ["alembic", "upgrade", "head"],
        check=True,
        cwd="/app",
        env={**__import__("os").environ, "PYTHONPATH": "/app"},
    )
    yield


app = FastAPI(title="PayTrack API", version="1.0.0", lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(auth.router)
app.include_router(bank_accounts.router)
app.include_router(rules.router)
app.include_router(transactions.router)
app.include_router(dashboard.router)
app.include_router(batch.router)
app.include_router(fund_transfers.router)


@app.get("/api/v1/health")
async def health():
    return {"status": "ok"}
