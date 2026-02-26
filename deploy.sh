#!/bin/bash

# ============================================
# ふるぼの - デプロイスクリプト
# ============================================
# 使用方法: ./deploy.sh
# 
# このスクリプトは本番環境（VPS）で実行されます。
# Gitから最新のコードを取得し、アプリケーションをデプロイします。

set -e  # エラーが発生したら即座に終了

# 色付きログ出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# ============================================
# デプロイ開始
# ============================================
log_info "🚀 デプロイを開始します..."
log_info "時刻: $(date '+%Y-%m-%d %H:%M:%S')"

# ============================================
# 1. Gitから最新のコードを取得
# ============================================
log_info "📥 最新のコードを取得中..."

# Gitリポジトリが存在するか確認
if [ ! -d .git ]; then
    log_error "Gitリポジトリが見つかりません。"
    exit 1
fi

# 現在のブランチを自動検出（引数で指定されていない場合）
if [ -z "$1" ]; then
    BRANCH=$(git branch --show-current 2>/dev/null || git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
    log_info "現在のブランチを検出: $BRANCH"
else
    BRANCH=$1
fi

# リモートの存在確認
if ! git remote | grep -q origin; then
    log_error "リモートリポジトリ 'origin' が見つかりません。"
    exit 1
fi

# 最新のコードを取得（ローカルの変更は破棄）
log_info "リモートから最新のコードを取得中..."
git fetch origin

# 現在のブランチがリモートに存在するか確認
if ! git ls-remote --heads origin "$BRANCH" | grep -q "$BRANCH"; then
    log_error "リモートブランチ 'origin/$BRANCH' が見つかりません。"
    exit 1
fi

# ローカルの変更を破棄してリモートの状態にリセット
git reset --hard origin/$BRANCH
log_success "コードを取得しました（ブランチ: $BRANCH）"

# ============================================
# 2. Composer依存関係のインストール
# ============================================
log_info "📦 Composer依存関係をインストール中..."

# Composerがインストールされているか確認
if ! command -v composer &> /dev/null; then
    log_error "Composerがインストールされていません。"
    exit 1
fi

# 本番環境用に最適化してインストール
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
log_success "Composer依存関係をインストールしました"

# ============================================
# 3. Node.js依存関係のインストールとアセットビルド
# ============================================
log_info "📦 Node.js依存関係をインストール中..."

# Node.jsがインストールされているか確認
if ! command -v node &> /dev/null; then
    log_error "Node.jsがインストールされていません。"
    exit 1
fi

# npmがインストールされているか確認
if ! command -v npm &> /dev/null; then
    log_error "npmがインストールされていません。"
    exit 1
fi

# 依存関係をインストール（package-lock.jsonを使用）
npm ci --production=false

log_info "🎨 アセットをビルド中..."
npm run build
log_success "アセットをビルドしました"

# ============================================
# 4. 環境設定ファイルの確認
# ============================================
log_info "⚙️  環境設定を確認中..."

if [ ! -f .env ]; then
    log_error ".envファイルが見つかりません。"
    log_error "本番環境用の.envファイルが必要です。"
    log_error "例: cp .env.example .env"
    log_error "その後、必要な環境変数を設定してください。"
    exit 1
else
    # .envファイルが存在する場合、APP_KEYの存在を確認
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null && ! grep -q "^APP_KEY=" .env 2>/dev/null; then
        log_warning "APP_KEYが設定されていない可能性があります。"
        log_warning "必要に応じて 'php artisan key:generate' を実行してください。"
    fi
    log_success ".envファイルを確認しました"
fi

# ============================================
# 5. Laravelキャッシュのクリア
# ============================================
log_info "🧹 キャッシュをクリア中..."

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true
log_success "キャッシュをクリアしました"

# ============================================
# 6. 設定の最適化（本番環境用）
# ============================================
log_info "⚡ 設定を最適化中..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
log_success "設定を最適化しました"

# ============================================
# 7. データベースマイグレーション
# ============================================
log_info "🗄️  データベースをマイグレート中..."

# マイグレーションを実行（--forceオプションで確認なし）
php artisan migrate --force
log_success "データベースをマイグレートしました"

# ============================================
# 8. ストレージリンクの作成
# ============================================
log_info "🔗 ストレージリンクを作成中..."

# 既にリンクが存在する場合はエラーを無視
php artisan storage:link || true
log_success "ストレージリンクを作成しました"

# ============================================
# 9. ファイル権限の設定
# ============================================
log_info "🔐 ファイル権限を設定中..."

# storageとbootstrap/cacheディレクトリに書き込み権限を付与
chmod -R 775 storage bootstrap/cache

# www-dataユーザーが存在する場合、所有権を設定
if id "www-data" &>/dev/null; then
    chown -R www-data:www-data storage bootstrap/cache
    log_success "ファイル権限を設定しました（www-data）"
else
    log_warning "www-dataユーザーが見つかりません。手動で権限を設定してください。"
fi

# ============================================
# 10. オプショナル: キュー再起動
# ============================================
# キューを使用している場合、コメントアウトを解除してください
# log_info "🔄 キューを再起動中..."
# php artisan queue:restart
# log_success "キューを再起動しました"

# ============================================
# 11. オプショナル: オペキャッシュのクリア
# ============================================
if command -v php &> /dev/null; then
    # PHPバージョンを取得
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "")
    
    if [ -n "$PHP_VERSION" ]; then
        PHP_FPM_SERVICE="php${PHP_VERSION}-fpm"
        
        # PHP-FPMサービスが存在し、アクティブか確認
        if systemctl list-units --type=service | grep -q "$PHP_FPM_SERVICE" && systemctl is-active --quiet "$PHP_FPM_SERVICE" 2>/dev/null; then
            log_info "🔄 PHP-FPMを再起動中... (サービス: $PHP_FPM_SERVICE)"
            sudo systemctl reload "$PHP_FPM_SERVICE" 2>/dev/null || {
                log_warning "PHP-FPMの再起動に失敗しました（権限の問題かもしれません）"
            }
            log_success "PHP-FPMを再起動しました"
        else
            log_info "PHP-FPMサービス ($PHP_FPM_SERVICE) が見つからないか、アクティブではありません。スキップします。"
        fi
    else
        log_warning "PHPバージョンを取得できませんでした。PHP-FPMの再起動をスキップします。"
    fi
fi

# ============================================
# デプロイ完了
# ============================================
log_success "🎉 デプロイが完了しました！"
log_info "時刻: $(date '+%Y-%m-%d %H:%M:%S')"
log_info "アプリケーションURL: https://hiraizumin.com/"
