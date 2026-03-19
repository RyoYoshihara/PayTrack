# PayTrack

入金・支払の予定および実績を管理し、抜け漏れ防止と業務効率化を実現するWebアプリケーション。

## 技術スタック

| レイヤー | 技術 |
|---------|------|
| フロントエンド | Next.js 14 (App Router) / TypeScript / Tailwind CSS |
| バックエンド | FastAPI / SQLAlchemy 2.0 (async) / Alembic |
| データベース | PostgreSQL 16 |
| 認証 | JWT (access + refresh token) |
| インフラ | Docker Compose |

## ディレクトリ構成

```
PayTrack/
├── docker-compose.yml      # 全サービス一括起動
├── .env.example             # 環境変数テンプレート
├── backend/                 # FastAPI バックエンド
│   ├── Dockerfile
│   ├── requirements.txt
│   ├── alembic/             # DBマイグレーション
│   └── app/
│       ├── main.py          # エントリーポイント
│       ├── config.py        # 設定
│       ├── database.py      # DB接続
│       ├── dependencies.py  # 認証依存関数
│       ├── models/          # SQLAlchemy モデル
│       ├── schemas/         # Pydantic スキーマ
│       ├── services/        # ビジネスロジック
│       └── routers/         # APIルーター
├── frontend/                # Next.js フロントエンド
│   ├── Dockerfile
│   ├── package.json
│   └── src/
│       ├── app/             # ページ（App Router）
│       ├── components/      # UIコンポーネント
│       ├── lib/             # API クライアント・認証
│       └── types/           # TypeScript 型定義
└── docs/                    # 設計書
```

## セットアップ

### 前提条件

- Docker / Docker Compose がインストールされていること

### 起動手順

1. 環境変数ファイルを作成

```bash
cp .env.example .env
```

2. Docker Compose で起動

```bash
docker compose up --build
```

3. アクセス

| サービス | URL |
|---------|-----|
| フロントエンド | http://localhost:3000 |
| バックエンドAPI | http://localhost:8000 |
| Swagger UI | http://localhost:8000/docs |

起動時にAlembicマイグレーションが自動実行され、DBテーブルが作成されます。

### 停止

```bash
docker compose down
```

DBデータを含めて完全に削除する場合:

```bash
docker compose down -v
```

## 主な機能

- **認証**: サインアップ / ログイン / ログアウト / トークンリフレッシュ
- **ダッシュボード**: 月次の入金合計・支払合計・差引残高・ステータス別件数
- **入出金管理**: 一覧表示 / 手動登録 / ステータス更新（完了・キャンセル）
- **ルール設定**: 繰り返しルール登録（毎月/単発）/ 一覧 / 削除
- **バッチ処理**: 月次データ自動生成 / 繰越処理

## 画面一覧

| 画面 | パス | 説明 |
|------|------|------|
| ログイン | /login | メール・パスワードで認証 |
| サインアップ | /signup | アカウント作成 |
| ダッシュボード | /dashboard | 月次サマリー表示 |
| 入出金一覧 | /transactions | 月別の入出金一覧・フィルタ・ステータス変更 |
| 入出金登録 | /transactions/new | 手動で入出金を登録 |
| ルール一覧 | /rules | 繰り返しルールの管理 |
| ルール登録 | /rules/new | 新規ルール作成 |

## API エンドポイント

詳細は [API設計書](docs/04.API設計書.md) を参照。

| メソッド | パス | 説明 |
|---------|------|------|
| POST | /api/v1/auth/signup | サインアップ |
| POST | /api/v1/auth/login | ログイン |
| POST | /api/v1/auth/refresh | トークンリフレッシュ |
| POST | /api/v1/auth/logout | ログアウト |
| GET/POST | /api/v1/rules | ルール一覧/登録 |
| GET/PUT/DELETE | /api/v1/rules/{id} | ルール詳細/更新/削除 |
| GET/POST | /api/v1/transactions | トランザクション一覧/登録 |
| GET/PUT/DELETE | /api/v1/transactions/{id} | 詳細/更新/削除 |
| PATCH | /api/v1/transactions/{id}/status | ステータス更新 |
| GET | /api/v1/dashboard/summary | 月次サマリー |
| POST | /api/v1/batch/generate | 月次データ生成 |
| POST | /api/v1/batch/carry-over | 繰越処理 |

## ドキュメント

| ファイル | 内容 |
|---------|------|
| [01.システム概要書](docs/01.システム概要書.md) | システムの目的・概要・技術スタック |
| [02.要件定義書](docs/02.要件定義書.md) | 機能要件・非機能要件・画面要件 |
| [03.DB設計書](docs/03.DB設計書.md) | テーブル定義・ER図・DDL |
| [04.API設計書](docs/04.API設計書.md) | エンドポイント・リクエスト/レスポンス仕様 |
| [05.ディレクトリ構成書](docs/05.ディレクトリ構成書.md) | プロジェクト構成・各ファイルの役割 |
