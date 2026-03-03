#!/bin/bash

###############################################################################
# パーミッション修正スクリプト
# 
# 用途: 本番環境でのデプロイ後にファイルパーミッションを修正
# 実行タイミング: デプロイ完了後、またはパーミッションエラー発生時
# 実行方法: ./scripts/fix-permissions.sh
###############################################################################

set -e  # エラーが発生したら即座に終了

# カラー定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ログ出力関数
log_info() {
    echo -e "${BLUE}ℹ ${NC} $1"
}

log_success() {
    echo -e "${GREEN}✓${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

# ヘッダー表示
echo ""
echo "================================================"
echo "  パーミッション修正スクリプト"
echo "================================================"
echo ""

# Dockerコンテナ名を取得
CONTAINER_NAME="chihopj-laravel.test-1"

# コンテナが起動しているか確認
log_info "Dockerコンテナの状態を確認中..."
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    log_error "Dockerコンテナ '$CONTAINER_NAME' が起動していません"
    log_info "以下のコマンドでコンテナを起動してください："
    echo "  ./vendor/bin/sail up -d"
    exit 1
fi
log_success "Dockerコンテナが起動しています"

# storageディレクトリのパーミッション修正
echo ""
log_info "storageディレクトリのパーミッションを修正中..."
docker exec "$CONTAINER_NAME" chown -R www-data:www-data /var/www/html/storage
docker exec "$CONTAINER_NAME" chmod -R 775 /var/www/html/storage
log_success "storageディレクトリのパーミッション修正完了"

# bootstrap/cacheディレクトリのパーミッション修正
echo ""
log_info "bootstrap/cacheディレクトリのパーミッションを修正中..."
docker exec "$CONTAINER_NAME" chown -R www-data:www-data /var/www/html/bootstrap/cache
docker exec "$CONTAINER_NAME" chmod -R 775 /var/www/html/bootstrap/cache
log_success "bootstrap/cacheディレクトリのパーミッション修正完了"

# キャッシュクリア
echo ""
log_info "キャッシュをクリア中..."
./vendor/bin/sail artisan config:clear > /dev/null 2>&1
log_success "設定キャッシュをクリアしました"

./vendor/bin/sail artisan cache:clear > /dev/null 2>&1
log_success "アプリケーションキャッシュをクリアしました"

./vendor/bin/sail artisan view:clear > /dev/null 2>&1
log_success "ビューキャッシュをクリアしました"

# パーミッション確認
echo ""
log_info "パーミッション設定を確認中..."
echo ""
echo "storage/framework/views:"
docker exec "$CONTAINER_NAME" ls -la /var/www/html/storage/framework/views | head -5
echo ""
echo "bootstrap/cache:"
docker exec "$CONTAINER_NAME" ls -la /var/www/html/bootstrap/cache | head -5

# 完了メッセージ
echo ""
echo "================================================"
log_success "パーミッション修正が完了しました！"
echo "================================================"
echo ""
log_info "次のステップ："
echo "  1. ブラウザで https://hiraizumin.com にアクセス"
echo "  2. 各ページが正常に表示されることを確認"
echo "  3. エラーログを確認: tail -f storage/logs/laravel.log"
echo ""
