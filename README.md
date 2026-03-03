# ふるぼの - 地方プロボノマッチングアプリケーション

## 概要
「ふるぼの」は、地方貢献に興味のある人材と地方の中小企業や自治体をつなぐプロボノマッチングアプリケーションです。

## 技術スタック
- **フレームワーク**: Laravel 12.x
- **フロントエンド**: Livewire 3 + Volt (Functional)
- **UIライブラリ**: Flux UI Free
- **スタイリング**: Tailwind CSS v4
- **テスト**: Pest v4
- **認証**: Laravel Fortify
- **開発環境**: Laravel Sail (Docker)

## 開発環境のセットアップ

### 前提条件
- Docker Desktop がインストールされていること
- Git がインストールされていること

### セットアップ手順

```bash
# リポジトリをクローン
git clone git@github.com:kabumotohomare/chihopj.git
cd chihopj

# 依存関係をインストール
composer install

# 環境変数ファイルをコピー
cp .env.example .env

# アプリケーションキーを生成
php artisan key:generate

# Sailを起動
./vendor/bin/sail up -d

# データベースをセットアップ
./vendor/bin/sail artisan migrate --seed

# フロントエンドアセットをビルド
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### アクセス
- **アプリケーション**: http://localhost
- **データベース**: localhost:3306

## デプロイ

### クイックデプロイ
詳細は [DEPLOY_QUICK_REFERENCE.md](DEPLOY_QUICK_REFERENCE.md) を参照してください。

```bash
# 開発環境: ブランチをmainにマージ
git checkout main
git merge <your-branch> --no-ff
git push origin main

# 本番環境: デプロイスクリプトを実行
ssh laravel@160.251.15.108
cd /var/www/chihopj
bash deploy.sh
```

### 詳細なデプロイ手順
完全なデプロイ手順、トラブルシューティング、ロールバック方法については、以下のドキュメントを参照してください:
- **詳細版**: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
- **クイックリファレンス**: [DEPLOY_QUICK_REFERENCE.md](DEPLOY_QUICK_REFERENCE.md)
- **エラー対応**: [DEPLOY_FIX.md](DEPLOY_FIX.md)

## テスト

```bash
# 全テストを実行
./vendor/bin/sail artisan test

# 特定のテストファイルを実行
./vendor/bin/sail artisan test tests/Feature/ExampleTest.php

# フィルタを使用してテストを実行
./vendor/bin/sail artisan test --filter=testName
```

## コーディング規約

### PHP
- PSR-12準拠
- 型宣言必須: `declare(strict_types=1);`
- PHPDocブロック優先
- コメントは日本語で記述

### Livewire Volt
- Functionalスタイル必須
- クラスベースコンポーネント禁止
- `sail artisan make:volt` 実行時は `--functional` オプション必須

### コードフォーマット
変更を確定する前に、必ずLaravel Pintを実行してください:

```bash
./vendor/bin/sail composer pint
```

## プロジェクト構造

```
chihopj/
├── app/
│   ├── Http/
│   │   └── Middleware/      # カスタムミドルウェア
│   ├── Models/              # Eloquentモデル
│   └── Policies/            # 認可ポリシー
├── database/
│   ├── migrations/          # データベースマイグレーション
│   └── seeders/             # シーダー
├── resources/
│   └── views/
│       └── livewire/        # Voltコンポーネント
├── routes/
│   └── web.php              # Webルート定義
├── tests/
│   ├── Feature/             # 機能テスト
│   └── Unit/                # ユニットテスト
├── deploy.sh                # デプロイスクリプト
├── DEPLOYMENT_GUIDE.md      # 詳細デプロイ手順
├── DEPLOY_QUICK_REFERENCE.md # クイックリファレンス
└── DEPLOY_FIX.md            # エラー対応手順
```

## 主要機能

### 認証・アカウント管理
- ユーザー登録・ログイン
- 2要素認証
- パスワードリセット

### プロフィール管理
- 企業プロフィール（登録・編集・詳細表示）
- ワーカープロフィール（登録・編集・詳細表示）

### 募集管理
- 募集の投稿・編集・削除
- 募集一覧・詳細表示
- 検索・フィルタ機能

### 応募管理
- 募集への応募
- 応募一覧（ワーカー向け・企業向け）
- 応募の承認・不承認・辞退

### チャット機能
- 1対1チャット
- リアルタイムメッセージング
- 既読管理

## ライセンス
このプロジェクトは非公開プロジェクトです。

## 連絡先
開発に関する質問や問題がある場合は、開発チームに連絡してください。
