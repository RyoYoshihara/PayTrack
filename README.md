# PayTrack

入金・支払の予定および実績を管理し、抜け漏れ防止と業務効率化を実現するWebアプリケーション。

## 技術スタック

| レイヤー | 技術 |
|---------|------|
| フレームワーク | Laravel 11 (PHP 8.3) |
| テンプレート | Blade + Tailwind CSS + Alpine.js |
| データベース | MySQL 8.0 |
| 認証 | Laravel Breeze (セッション認証) |
| インフラ | Docker Compose (nginx + PHP-FPM + MySQL) |

## ディレクトリ構成

```
PayTrack/
├── docker-compose.laravel.yml   # Docker Compose 設定
├── docker/
│   ├── nginx/default.conf       # nginx 設定
│   └── php/Dockerfile           # PHP-FPM イメージ
├── laravel/                     # Laravel アプリケーション
│   ├── app/
│   │   ├── Models/              # Eloquent モデル
│   │   ├── Services/            # ビジネスロジック
│   │   ├── Http/
│   │   │   ├── Controllers/     # コントローラ
│   │   │   └── Requests/        # フォームバリデーション
│   │   └── ...
│   ├── database/migrations/     # マイグレーション
│   ├── resources/views/         # Blade テンプレート
│   │   ├── layouts/             # レイアウト
│   │   ├── components/          # 共通コンポーネント
│   │   ├── dashboard/           # ダッシュボード
│   │   ├── schedule/            # 収支スケジュール
│   │   ├── transactions/        # 取引管理
│   │   ├── rules/               # ルール設定
│   │   ├── bank-accounts/       # 口座管理
│   │   └── fund-transfers/      # 口座振替
│   ├── routes/web.php           # ルーティング
│   └── lang/ja/                 # 日本語メッセージ
└── docs/                        # 設計書
```

## セットアップ

### 前提条件

- Docker / Docker Compose がインストールされていること

### 起動手順

1. Docker Compose でビルド・起動

```bash
docker compose -f docker-compose.laravel.yml up -d --build
```

2. マイグレーション実行

```bash
docker compose -f docker-compose.laravel.yml exec app php artisan migrate --force
```

3. 初期アカウント作成

```bash
docker compose -f docker-compose.laravel.yml exec app php artisan db:seed --force
```

以下の初期アカウントが作成されます。ログイン後、サイドバーの「アカウント情報」からメールアドレス・パスワードを変更してください。

| 項目 | 値 |
|------|------|
| メールアドレス | test@isect.jp |
| パスワード | Client55 |

3. アクセス

| サービス | URL |
|---------|-----|
| アプリケーション | http://localhost:8090 |

初回アクセス時にユーザー登録画面からアカウントを作成してください。

### 停止

```bash
docker compose -f docker-compose.laravel.yml down
```

DBデータを含めて完全に削除する場合:

```bash
docker compose -f docker-compose.laravel.yml down -v
```

## 主な機能

- **認証**: ユーザー登録 / ログイン / ログアウト（セッション認証）
- **ダッシュボード**: 月次の収入合計・支出合計・収支バランス・ステータス別件数・口座別サマリー
- **収支スケジュール**: 予定・繰越中の取引を一覧表示、完了・繰越・取消操作
- **取引管理**: 一覧表示（月別・タイプ・ステータスでフィルタ）/ 登録 / 編集 / 削除
- **ルール設定**: 繰り返しルール登録（毎月/一回のみ）/ 編集 / 削除、ルールから取引を自動生成
- **口座管理**: 口座の登録 / 編集 / 削除 / 並び替え
- **口座振替**: 口座間の振替登録、双方確認による自動完了
- **一括操作**: 当月のデータ一括生成 / 繰り越し処理

## 画面一覧

| 画面 | パス | 説明 |
|------|------|------|
| ログイン | /login | メール・パスワードで認証 |
| ユーザー登録 | /register | アカウント作成 |
| ダッシュボード | /dashboard | 月次サマリー・口座別サマリー・一括操作 |
| 収支スケジュール | /schedule | 予定・繰越中の取引管理 |
| 取引一覧 | /transactions | 月別の取引一覧・フィルタ |
| 取引登録 | /transactions/create | 新規取引の登録 |
| 取引編集 | /transactions/{id}/edit | 取引の編集 |
| ルール一覧 | /rules | ルールの管理 |
| ルール登録 | /rules/create | 新規ルール作成 |
| ルール編集 | /rules/{id}/edit | ルールの編集 |
| 口座一覧 | /bank-accounts | 口座の管理・並び替え |
| 口座登録 | /bank-accounts/create | 新規口座の登録 |
| 口座編集 | /bank-accounts/{id}/edit | 口座の編集 |
| 振替一覧 | /fund-transfers | 口座振替の管理 |
| 振替登録 | /fund-transfers/create | 新規振替の登録 |

## ドキュメント

| ファイル | 内容 |
|---------|------|
| [01.システム概要書](docs/01.システム概要書.md) | システムの目的・概要・技術スタック |
| [02.要件定義書](docs/02.要件定義書.md) | 機能要件・非機能要件・画面要件 |
| [03.DB設計書](docs/03.DB設計書.md) | テーブル定義・ER図・DDL |
| [04.API設計書](docs/04.API設計書.md) | エンドポイント・リクエスト/レスポンス仕様 |
| [05.ディレクトリ構成書](docs/05.ディレクトリ構成書.md) | プロジェクト構成・各ファイルの役割 |


## 本番環境　適用操作手順

cd ~/paytrack

git reset --hard
git clean -fd

git pull

cd ~/paytrack/laravel

php ~/bin/composer install --no-dev --optimize-autoloader

php artisan migrate

php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan view:clear