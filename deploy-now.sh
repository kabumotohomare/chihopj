#!/bin/bash

################################################################################
# 即座にデプロイを実行するスクリプト（確認なし）
################################################################################

set -e

# 色付き出力用の定数
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 本番サーバー情報
PROD_HOST="hiraizumi-conoha-root"
PROD_PATH="/var/www/chihopj"
LOCAL_PATH="/home/laravel/camp/100_laravel/chihopj"

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

log_info "=== デプロイ開始 ==="
log_info "日時: $(date '+%Y-%m-%d %H:%M:%S')"
log_info "サーバー: ${PROD_HOST}"
echo ""

# 1. メンテナンスモードを有効化
log_step "Step 1/10: メンテナンスモードを有効化"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan down" || log_warning "既にメンテナンスモード"

# 2. ファイルを同期
log_step "Step 2/10: ファイルを同期中..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/app/private/*' \
    --exclude='storage/app/public/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*' \
    ${LOCAL_PATH}/ ${PROD_HOST}:${PROD_PATH}/ > /dev/null 2>&1
log_info "✅ ファイル同期完了"

# 3. Composer
log_step "Step 3/10: Composer依存関係を更新"
ssh ${PROD_HOST} "cd ${PROD_PATH} && composer install --no-dev --optimize-autoloader --no-interaction" > /dev/null 2>&1
log_info "✅ Composer完了"

# 4. npm
log_step "Step 4/10: フロントエンドアセットをビルド"
ssh ${PROD_HOST} "cd ${PROD_PATH} && npm ci --production=false > /dev/null 2>&1 && npm run build > /dev/null 2>&1"
log_info "✅ ビルド完了"

# 5. キャッシュクリア
log_step "Step 5/10: キャッシュをクリア"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear" > /dev/null 2>&1
log_info "✅ キャッシュクリア完了"

# 6. キャッシュ再構築
log_step "Step 6/10: キャッシュを再構築"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan config:cache && php artisan route:cache && php artisan view:cache" > /dev/null 2>&1
log_info "✅ キャッシュ再構築完了"

# 7. マイグレーション
log_step "Step 7/10: データベースマイグレーション"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan migrate --force" > /dev/null 2>&1
log_info "✅ マイグレーション完了"

# 8. ストレージリンク
log_step "Step 8/10: ストレージリンクを確認"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan storage:link" > /dev/null 2>&1 || log_info "リンク既存"

# 9. 権限設定
log_step "Step 9/10: 権限を設定"
ssh ${PROD_HOST} "cd ${PROD_PATH} && chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache" > /dev/null 2>&1
log_info "✅ 権限設定完了"

# 10. メンテナンスモード解除
log_step "Step 10/10: メンテナンスモードを解除"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan up"
log_info "✅ メンテナンスモード解除"

echo ""
log_info "🎉 デプロイが正常に完了しました！"
log_info "完了日時: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
log_info "📋 動作確認:"
log_info "  - トップページ: http://133.88.118.54"
log_info "  - ログイン画面: http://133.88.118.54/login"
echo ""
log_info "📝 エラーログ確認:"
echo "  ssh ${PROD_HOST} 'tail -100 ${PROD_PATH}/storage/logs/laravel.log'"
