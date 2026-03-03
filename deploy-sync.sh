#!/bin/bash

################################################################################
# rsyncベースのデプロイスクリプト
# 
# 使用方法:
#   bash deploy-sync.sh
#
# このスクリプトはrsyncでファイルを同期してデプロイを実行します
################################################################################

set -e

# 色付き出力用の定数
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 本番サーバー情報
PROD_HOST="hiraizumi-conoha-root"
PROD_PATH="/var/www/chihopj"
LOCAL_PATH="/home/laravel/camp/100_laravel/chihopj"

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

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# デプロイ開始
log_info "デプロイを開始します..."
echo ""

# SSH接続テスト
log_step "本番サーバーへの接続を確認中..."
if ! ssh ${PROD_HOST} "echo 'Connection OK'" 2>/dev/null; then
    log_error "本番サーバーへの接続に失敗しました"
    exit 1
fi
log_info "✅ 本番サーバーへの接続成功"
echo ""

# デプロイ実行の確認
log_warning "本番環境にデプロイを実行します"
log_info "サーバー: ${PROD_HOST}"
log_info "パス: ${PROD_PATH}"
echo ""
read -p "続行しますか? (yes/no): " -r
echo ""
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    log_info "デプロイをキャンセルしました"
    exit 0
fi

# 1. メンテナンスモードを有効化
log_step "Step 1: メンテナンスモードを有効化"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan down" || log_warning "メンテナンスモードの有効化に失敗"
echo ""

# 2. ファイルを同期（除外リスト付き）
log_step "Step 2: ファイルを同期中..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/app/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*' \
    ${LOCAL_PATH}/ ${PROD_HOST}:${PROD_PATH}/
log_info "✅ ファイル同期完了"
echo ""

# 3. Composer の依存関係を更新
log_step "Step 3: Composer の依存関係を更新"
ssh ${PROD_HOST} "cd ${PROD_PATH} && composer install --no-dev --optimize-autoloader --no-interaction"
echo ""

# 4. npm の依存関係を更新してビルド
log_step "Step 4: フロントエンドアセットをビルド"
ssh ${PROD_HOST} "cd ${PROD_PATH} && npm ci --production=false && npm run build"
echo ""

# 5. キャッシュをクリア
log_step "Step 5: キャッシュをクリア"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"
echo ""

# 6. 設定とルートをキャッシュ
log_step "Step 6: 設定とルートをキャッシュ"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan config:cache && php artisan route:cache && php artisan view:cache"
echo ""

# 7. マイグレーションを実行
log_step "Step 7: データベースマイグレーションを実行"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan migrate --force"
echo ""

# 8. ストレージのシンボリックリンクを作成
log_step "Step 8: ストレージのシンボリックリンクを確認"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan storage:link" || log_warning "シンボリックリンクは既に存在します"
echo ""

# 9. 権限を設定
log_step "Step 9: ディレクトリの権限を設定"
ssh ${PROD_HOST} "cd ${PROD_PATH} && chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"
echo ""

# 10. メンテナンスモードを解除
log_step "Step 10: メンテナンスモードを解除"
ssh ${PROD_HOST} "cd ${PROD_PATH} && php artisan up"
echo ""

log_info "✅ デプロイが正常に完了しました！"
log_info "デプロイ日時: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
log_info "動作確認:"
log_info "  - トップページ: http://133.88.118.54"
log_info "  - ログイン画面: http://133.88.118.54/login"
echo ""
log_info "エラーログ確認:"
echo "  ssh ${PROD_HOST} 'tail -100 ${PROD_PATH}/storage/logs/laravel.log'"
