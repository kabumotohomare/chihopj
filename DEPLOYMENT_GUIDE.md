# デプロイ手順書

## 概要
このドキュメントは、`kabumoto-front-1` ブランチの変更を本番環境にデプロイする手順を説明します。

## デプロイ対象の変更内容

### 今回のデプロイ内容（kabumoto-front-1ブランチ）
- **ログイン画面のUI改善**
  - ロゴ画像を更新（`public/images/presets/logo.png`）
  - レイアウトを簡素化し、ユーザビリティを向上
  - 不要なマイグレーションファイルを削除
- **ストレージディレクトリの権限設定を最適化**
  - `.gitignore` ファイルの実行権限を付与

### コミット履歴
```
e6ed076 フロントエンド修正
1d2c5fb 開発環境を本番環境に合わせた
879757e データベース修正
878ce32 デプロイスクリプトと手順書を追加
081e3c8 デプロイエラーが発生しており確認中
```

---

## 前提条件

### 開発環境
- Git がインストールされている
- GitHub への SSH アクセスが設定されている
- ブランチ `kabumoto-front-1` がリモートにプッシュ済み

### 本番環境
- **サーバー情報**: 160.251.15.108
- **ユーザー**: laravel
- **アプリケーションディレクトリ**: `/var/www/chihopj`
- **PHP バージョン**: 8.5 以上
- **Composer**: インストール済み
- **Node.js / npm**: インストール済み
- **Webサーバー**: Nginx + PHP-FPM
- **データベース**: MariaDB

---

## デプロイ手順

### Phase 1: 開発環境での準備作業

#### 1.1 ブランチのマージ

```bash
# 作業ディレクトリに移動
cd /home/laravel/camp/100_laravel/chihopj

# 現在のブランチを確認
git status
# 出力: On branch kabumoto-front-1

# mainブランチに切り替え
git checkout main

# 最新のmainブランチを取得
git pull origin main

# kabumoto-front-1ブランチをmainにマージ
git merge kabumoto-front-1 --no-ff -m "Merge kabumoto-front-1 into main"

# マージ結果を確認
git log --oneline -5

# リモートにプッシュ
git push origin main
```

**実行結果の確認ポイント:**
- マージコンフリクトが発生しないこと
- プッシュが成功すること（`To github.com:kabumotohomare/chihopj.git`）

#### 1.2 デプロイスクリプトの準備

デプロイスクリプト（`deploy.sh`）が作成されていることを確認します。

```bash
ls -la deploy.sh
# 実行権限があることを確認（-rwxr-xr-x）
```

---

### Phase 2: 本番環境へのデプロイ

#### 2.1 本番サーバーへのSSH接続

```bash
# 本番サーバーにSSH接続
ssh laravel@160.251.15.108

# 接続成功後、アプリケーションディレクトリに移動
cd /var/www/chihopj
```

**トラブルシューティング:**
- SSH接続がタイムアウトする場合:
  - ファイアウォールの設定を確認
  - サーバーのSSHサービスが起動しているか確認（`sudo systemctl status sshd`）
  - VPNやセキュリティグループの設定を確認

#### 2.2 現在の状態を確認

```bash
# 現在のブランチとコミットを確認
git status
git log -1 --oneline

# 現在のPHPバージョンを確認
php -v

# Composerのバージョンを確認
composer --version

# Node.jsとnpmのバージョンを確認
node -v
npm -v

# データベース接続を確認
php artisan tinker --execute="DB::connection()->getPdo();"
```

#### 2.3 デプロイスクリプトの実行

```bash
# デプロイスクリプトを実行
bash deploy.sh
```

**スクリプトの実行内容:**
1. メンテナンスモードを有効化（`php artisan down`）
2. 最新のコードを取得（`git fetch` + `git reset --hard origin/main`）
3. Composerの依存関係を更新（`composer install --no-dev --optimize-autoloader`）
4. フロントエンドアセットをビルド（`npm ci` + `npm run build`）
5. キャッシュをクリア（config, cache, route, view）
6. 設定とルートをキャッシュ（config:cache, route:cache, view:cache）
7. データベースマイグレーションを実行（`php artisan migrate --force`）
8. ストレージのシンボリックリンクを確認（`php artisan storage:link`）
9. ディレクトリの権限を設定（`chmod -R 775 storage bootstrap/cache`）
10. メンテナンスモードを解除（`php artisan up`）

**各ステップの確認ポイント:**
- エラーが発生せずに全ステップが完了すること
- 最終的に「✅ デプロイが正常に完了しました！」と表示されること

#### 2.4 デプロイ後の動作確認

```bash
# アプリケーションのヘルスチェック
curl -I http://160.251.15.108

# ログを確認（エラーがないか）
tail -f storage/logs/laravel.log

# キューワーカーが動作している場合は再起動
sudo systemctl restart laravel-worker
```

---

### Phase 3: 動作確認

#### 3.1 ブラウザでの確認

1. **トップページ**: http://160.251.15.108
   - ページが正常に表示されること
   - 新しいロゴが表示されること

2. **ログイン画面**: http://160.251.15.108/login
   - レイアウトが改善されていること
   - ロゴが正しく表示されること
   - ログイン機能が正常に動作すること

3. **認証後の画面**: 
   - ダッシュボードが正常に表示されること
   - 各機能が正常に動作すること

#### 3.2 エラーログの確認

```bash
# 本番環境でエラーログを確認
tail -100 storage/logs/laravel.log

# Nginxのエラーログも確認
sudo tail -100 /var/log/nginx/error.log
```

---

## ロールバック手順

デプロイ後に問題が発生した場合のロールバック手順です。

### 手順1: 以前のコミットに戻す

```bash
# 本番サーバーにSSH接続
ssh laravel@160.251.15.108
cd /var/www/chihopj

# メンテナンスモードを有効化
php artisan down

# 以前のコミットを確認
git log --oneline -10

# 以前のコミットに戻す（例: 1d2c5fb）
git reset --hard 1d2c5fb

# Composerの依存関係を更新
composer install --no-dev --optimize-autoloader

# フロントエンドアセットを再ビルド
npm ci
npm run build

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 設定をキャッシュ
php artisan config:cache
php artisan route:cache
php artisan view:cache

# メンテナンスモードを解除
php artisan up
```

### 手順2: データベースのロールバック（必要な場合）

```bash
# マイグレーションを1つ戻す
php artisan migrate:rollback --step=1

# または特定のバッチまで戻す
php artisan migrate:rollback --batch=N
```

---

## トラブルシューティング

### 問題1: SSH接続がタイムアウトする

**原因:**
- ファイアウォールがSSHポート（22）をブロックしている
- サーバーがダウンしている
- ネットワークの問題

**解決方法:**
```bash
# サーバーのステータスを確認（別の管理ツールから）
# ファイアウォールの設定を確認
sudo ufw status
sudo ufw allow 22/tcp

# SSHサービスを再起動
sudo systemctl restart sshd
```

### 問題2: "could not find driver" エラー

**原因:**
PHPのPDO MySQL拡張機能がインストールされていない

**解決方法:**
```bash
# PHP拡張機能をインストール
sudo apt-get update
sudo apt-get install php8.5-mysql

# PHP-FPMを再起動
sudo systemctl restart php8.5-fpm

# 確認
php -m | grep pdo_mysql
```

### 問題3: npm ビルドエラー

**原因:**
- Node.jsのバージョンが古い
- メモリ不足

**解決方法:**
```bash
# Node.jsのバージョンを確認
node -v

# メモリを増やしてビルド
NODE_OPTIONS="--max-old-space-size=4096" npm run build
```

### 問題4: 権限エラー

**原因:**
ストレージディレクトリの権限が不適切

**解決方法:**
```bash
# 権限を修正
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# SELinuxが有効な場合
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

---

## チェックリスト

### デプロイ前
- [ ] 開発環境でテストが全て通過している
- [ ] ブランチがリモートにプッシュされている
- [ ] データベースのバックアップを取得している
- [ ] デプロイスクリプトが準備されている

### デプロイ中
- [ ] メンテナンスモードが有効化されている
- [ ] 最新のコードが取得されている
- [ ] Composerの依存関係が更新されている
- [ ] フロントエンドアセットがビルドされている
- [ ] キャッシュがクリアされている
- [ ] マイグレーションが実行されている
- [ ] 権限が正しく設定されている

### デプロイ後
- [ ] メンテナンスモードが解除されている
- [ ] トップページが正常に表示される
- [ ] ログイン機能が正常に動作する
- [ ] エラーログにエラーがない
- [ ] 主要な機能が正常に動作する

---

## 連絡先

デプロイに関する問題が発生した場合は、以下に連絡してください:

- **開発チーム**: [連絡先情報]
- **インフラチーム**: [連絡先情報]

---

## 変更履歴

| 日付 | バージョン | 変更内容 | 担当者 |
|------|-----------|---------|--------|
| 2026-03-03 | 1.0 | 初版作成 | - |

---

## 補足資料

### 関連ドキュメント
- `DEPLOY_FIX.md`: マイグレーションエラーの修正手順
- `deploy.sh`: デプロイスクリプト
- `README.md`: プロジェクト概要

### 参考リンク
- [Laravel デプロイメントドキュメント](https://laravel.com/docs/12.x/deployment)
- [GitHub リポジトリ](https://github.com/kabumotohomare/chihopj)
