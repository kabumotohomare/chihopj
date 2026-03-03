#!/bin/bash

################################################################################
# リモートデプロイスクリプト
# 
# 使用方法:
#   bash deploy-remote.sh
#
# このスクリプトは本番サーバーにSSH接続してデプロイを実行します
################################################################################

set -e

# 色付き出力用の定数
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 本番サーバー情報
PROD_USER="laravel"
PROD_HOST="160.251.15.108"
PROD_PATH="/var/www/chihopj"

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

# SSH接続テスト
log_step "本番サーバーへの接続を確認中..."
if ! ssh -o ConnectTimeout=10 -o BatchMode=yes ${PROD_USER}@${PROD_HOST} "echo 'Connection OK'" 2>/dev/null; then
    log_error "本番サーバーへの接続に失敗しました"
    log_info "以下を確認してください:"
    log_info "  1. SSHキーが正しく設定されているか"
    log_info "  2. サーバーが起動しているか"
    log_info "  3. ファイアウォールの設定"
    log_info "  4. VPN接続が必要か"
    echo ""
    log_info "手動でデプロイする場合:"
    echo "  ssh ${PROD_USER}@${PROD_HOST}"
    echo "  cd ${PROD_PATH}"
    echo "  bash deploy.sh"
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

# リモートでデプロイスクリプトを実行
log_step "本番サーバーでデプロイを実行中..."
echo ""

ssh ${PROD_USER}@${PROD_HOST} "cd ${PROD_PATH} && bash deploy.sh"

echo ""
log_info "✅ デプロイが完了しました！"
log_info "デプロイ日時: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
log_info "動作確認:"
log_info "  - トップページ: http://${PROD_HOST}"
log_info "  - ログイン画面: http://${PROD_HOST}/login"
echo ""
log_info "エラーログ確認:"
echo "  ssh ${PROD_USER}@${PROD_HOST} 'tail -100 ${PROD_PATH}/storage/logs/laravel.log'"
