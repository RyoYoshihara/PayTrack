from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.config import settings
from app.database import get_db
from app.dependencies import get_current_user
from app.models.user import User
from app.schemas.auth import (
    LoginRequest,
    LogoutRequest,
    RefreshRequest,
    SignupRequest,
    TokenResponse,
    UserResponse,
)
from app.services.auth import (
    blacklist_token,
    create_access_token,
    create_refresh_token,
    create_user,
    decode_token,
    get_user_by_email,
    is_token_blacklisted,
    verify_password,
)

router = APIRouter(prefix="/api/v1/auth", tags=["auth"])


@router.post("/signup", status_code=status.HTTP_201_CREATED)
async def signup(body: SignupRequest, db: AsyncSession = Depends(get_db)):
    existing = await get_user_by_email(db, body.email)
    if existing:
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail={"code": "CONFLICT", "message": "このメールアドレスは既に登録されています"},
        )
    user = await create_user(db, body.email, body.password)
    return {"data": UserResponse.model_validate(user)}


@router.post("/login")
async def login(body: LoginRequest, db: AsyncSession = Depends(get_db)):
    user = await get_user_by_email(db, body.email)
    if not user or not verify_password(body.password, user.password_hash):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "UNAUTHORIZED", "message": "メールアドレスまたはパスワードが正しくありません"},
        )
    return {
        "data": TokenResponse(
            access_token=create_access_token(user.id),
            refresh_token=create_refresh_token(user.id),
            expires_in=settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        )
    }


@router.post("/refresh")
async def refresh(body: RefreshRequest):
    if is_token_blacklisted(body.refresh_token):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "UNAUTHORIZED", "message": "無効なリフレッシュトークンです"},
        )
    payload = decode_token(body.refresh_token)
    if not payload or payload.get("type") != "refresh":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "UNAUTHORIZED", "message": "無効なリフレッシュトークンです"},
        )
    import uuid

    user_id = uuid.UUID(payload["sub"])
    blacklist_token(body.refresh_token)
    return {
        "data": TokenResponse(
            access_token=create_access_token(user_id),
            refresh_token=create_refresh_token(user_id),
            expires_in=settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        )
    }


@router.post("/logout")
async def logout(
    body: LogoutRequest, _: User = Depends(get_current_user)
):
    blacklist_token(body.refresh_token)
    return {"data": {"message": "ログアウトしました"}}
