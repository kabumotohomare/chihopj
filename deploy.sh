#!/bin/bash

################################################################################
# Laravel デプロイスクリプト
# 
# 使用方法:
#   本番環境で実行: bash deploy.sh
#
# 前提条件:
#   - 本番環境に Git がインストールされている
#   - 本番環境のディレクトリ: /var/www/chihopj
#   - PHP 8.5 以上がインストールされている
#   - Composer がインストールされている
#   - Node.js と npm がインストールされている
################################################################################

set -e  # エラーが発生したら即座に終了

# 色付き出力用の定数
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ログ出力関数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# デプロイ開始
log_info "デプロイを開始します..."

# 1. メンテナンスモードを有効化
log_info "Step 1: メンテナンスモードを有効化"
php artisan down || log_warning "メンテナンスモードの有効化に失敗しました（既に有効の可能性）"

# 2. 最新のコードを取得
log_info "Step 2: 最新のコードを取得"
git fetch origin
git reset --hard origin/main
log_info "最新のコミット: $(git log -1 --oneline)"

# 3. Composer の依存関係を更新
log_info "Step 3: Composer の依存関係を更新"
composer install --no-dev --optimize-autoloader --no-interaction

# 4. npm の依存関係を更新してビルド
log_info "Step 4: フロントエンドアセットをビルド"
npm ci --production=false
npm run build

# 5. キャッシュをクリア
log_info "Step 5: キャッシュをクリア"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. 設定とルートをキャッシュ
log_info "Step 6: 設定とルートをキャッシュ"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. マイグレーションを実行
log_info "Step 7: データベースマイグレーションを実行"
php artisan migrate --force

# 8. ストレージのシンボリックリンクを作成（存在しない場合）
log_info "Step 8: ストレージのシンボリックリンクを確認"
php artisan storage:link || log_warning "シンボリックリンクは既に存在します"

# 9. 権限を設定
log_info "Step 9: ディレクトリの権限を設定"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. メンテナンスモードを解除
log_info "Step 10: メンテナンスモードを解除"
php artisan up

log_info "✅ デプロイが正常に完了しました！"
log_info "デプロイ日時: $(date '+%Y-%m-%d %H:%M:%S')"
