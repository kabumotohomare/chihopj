# デプロイ クイックリファレンス

## 🚀 開発環境での作業

```bash
# 1. mainブランチに切り替えてマージ
cd /home/laravel/camp/100_laravel/chihopj
git checkout main
git pull origin main
git merge kabumoto-front-1 --no-ff -m "Merge kabumoto-front-1 into main"
git push origin main
```

---

## 🌐 本番環境でのデプロイ

```bash
# 2. 本番サーバーに接続
ssh laravel@160.251.15.108

# 3. アプリケーションディレクトリに移動
cd /var/www/chihopj

# 4. デプロイスクリプトを実行
bash deploy.sh
```

---

## ✅ 動作確認

```bash
# ブラウザで確認
# - http://160.251.15.108 (トップページ)
# - http://160.251.15.108/login (ログイン画面)

# エラーログを確認
tail -100 storage/logs/laravel.log
```

---

## 🔄 ロールバック（問題発生時）

```bash
ssh laravel@160.251.15.108
cd /var/www/chihopj

php artisan down
git reset --hard <前のコミットハッシュ>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 📋 チェックリスト

### デプロイ前
- [ ] テストが全て通過
- [ ] ブランチがプッシュ済み
- [ ] DBバックアップ取得済み

### デプロイ後
- [ ] トップページ表示OK
- [ ] ログイン機能OK
- [ ] エラーログ確認OK

---

## 🆘 トラブルシューティング

### SSH接続エラー
```bash
# ファイアウォール確認
sudo ufw status
sudo ufw allow 22/tcp
```

### データベースエラー
```bash
# PHP拡張機能インストール
sudo apt-get install php8.5-mysql
sudo systemctl restart php8.5-fpm
```

### 権限エラー
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

詳細は `DEPLOYMENT_GUIDE.md` を参照してください。
