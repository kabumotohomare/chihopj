# Docker 開発環境セットアップ手順

初回参画者向けのローカル開発環境構築ガイドです。

## 前提条件

- Docker Desktop（または Docker Engine + Docker Compose V2）がインストール済み
- Git がインストール済み
- Node.js 20+ がインストール済み（フロントエンドビルド用）

## 1. リポジトリのクローン

```bash
git clone <repository-url>
cd chihopj
```

## 2. 環境変数の設定

`.env.example` をコピーして `.env` を作成します。

```bash
cp .env.example .env
```

デフォルト値のまま動作します。変更が必要な場合のみ `.env` を編集してください。

| 変数 | デフォルト値 | 説明 |
|------|-------------|------|
| `DB_HOST` | `mysql` | Docker内のMySQLホスト名 |
| `DB_DATABASE` | `chihopj` | データベース名 |
| `DB_USERNAME` | `chihopj` | DBユーザー名 |
| `DB_PASSWORD` | `password` | DBパスワード |
| `APP_PORT` | `8080` | Nginxの公開ポート（compose.yaml） |

## 3. Docker コンテナの起動

```bash
docker compose up -d
```

初回はPHPイメージのビルドが走ります（数分かかります）。

コンテナが正常に起動したことを確認:

```bash
docker compose ps
```

`app`, `nginx`, `mysql` の3つが `running` であればOKです。

## 4. PHP 依存パッケージのインストール

```bash
docker compose exec app composer install
```

## 5. フロントエンドのビルド

```bash
npm install
npm run build
```

## 6. アプリケーションキーの生成

```bash
docker compose exec app php artisan key:generate
```

## 7. データベースのマイグレーション

```bash
docker compose exec app php artisan migrate
```

## 8. 初期データの投入

```bash
docker compose exec app php artisan db:seed
```

## 9. アクセス確認

http://localhost:8080 にアクセスし、画面が表示されればセットアップ完了です。

---

## アカウント一覧

`php artisan db:seed` で以下のアカウントが自動作成されます。
すべてのパスワードは `password` です。

| ロール | Email | 作成元 Seeder |
|--------|-------|---------------|
| 管理者 (admin) | admin@example.com | AdminUserSeeder |
| 役所 (municipal) | municipal@example.com | MunicipalUserSeeder |
| ワーカー (worker) | worker@example.com | DevelopmentSeeder（local環境のみ） |
| 企業 (company) | company@example.com | DevelopmentSeeder（local環境のみ） |
| テスト (worker) | test@example.com | DatabaseSeeder |

**補足**: 管理者・役所アカウントは全環境で作成されます。ワーカー・企業アカウントは `APP_ENV=local` または `development` のときのみ作成されます。

## ロール別アクセス権限

### 管理者 (admin)

| 画面 | URL | アクセス |
|------|-----|----------|
| 管理画面 | /admin | 可 |
| 役所パネル | /government | 不可 |
| フロント | / | 不可（専用画面なし） |

管理画面で利用可能な機能:
- 応募管理（一覧・詳細・編集・CSVダウンロード）
- ダッシュボード

### 役所 (municipal)

| 画面 | URL | アクセス |
|------|-----|----------|
| 役所パネル | /government | 可 |
| 管理画面 | /admin | 不可 |
| フロント | / | 不可（専用画面なし） |

役所パネルで利用可能な機能:
- 応募一覧（閲覧専用・CSVダウンロード）
- ダッシュボード

**注意**: 役所ユーザーは応募データの編集・削除はできません。

### ワーカー (worker)

| 画面 | URL | アクセス |
|------|-----|----------|
| フロント | / | 可 |
| 管理画面 | /admin | 不可 |
| 役所パネル | /government | 不可 |

フロントで利用可能な機能:
- 求人への応募
- プロフィール登録・編集
- チャット

### 企業 (company)

| 画面 | URL | アクセス |
|------|-----|----------|
| フロント | / | 可 |
| 管理画面 | /admin | 不可 |
| 役所パネル | /government | 不可 |

フロントで利用可能な機能:
- 求人投稿・管理
- 応募の承認・不承認
- チャット

## CSVエクスポート

管理画面・役所パネルの応募一覧から「CSVダウンロード」ボタンでエクスポートできます。

出力カラム:

| カテゴリ | カラム |
|----------|--------|
| 応募者情報 | 氏名、メールアドレス、ハンドルネーム、性別、生年月日、現住所、電話番号、出身地、居住地 |
| 応募内容 | 志望動機、応募理由、ステータス、応募日、判定日 |
| 求人情報 | 求人タイトル、企業名 |

**注意**: CSVエクスポートはキュー（`QUEUE_CONNECTION=database`）で非同期処理されます。エクスポート完了後に通知が届きます。

---

## よくあるトラブル

### ポートが競合する場合

`.env` で `APP_PORT` や `FORWARD_DB_PORT` を変更してください。

```env
APP_PORT=8081
FORWARD_DB_PORT=33061
```

### コンテナを完全にリセットしたい場合

```bash
docker compose down -v
docker compose up -d
```

`-v` オプションでボリューム（DBデータ）も削除されます。
リセット後は手順 4〜8 を再度実行してください。

### マイグレーションエラーが出る場合

MySQLの起動を待ってからリトライしてください。

```bash
docker compose exec app php artisan migrate
```

### artisan コマンドの実行

すべての artisan コマンドは `app` コンテナ内で実行します。

```bash
docker compose exec app php artisan <command>
```
