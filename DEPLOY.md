# デプロイ手順書

## 概要

このドキュメントでは、VPS（Ubuntu + Nginx）へのGitデプロイ手順を説明します。

**デプロイ先**: https://hiraizumin.com/

## クイックスタート（2回目以降のデプロイ）

既にVPSの準備が完了し、初回デプロイが済んでいる場合の最短手順：

```bash
# 1. VPSにSSH接続
# 注意: "user" は実際のユーザー名に置き換えてください（例: root, ubuntu, admin）
ssh 実際のユーザー名@hiraizumin.com

# SSH接続エラーが発生する場合は、「トラブルシューティング」セクションの「0. SSH接続エラー」を参照してください

# 2. アプリケーションディレクトリに移動
cd /var/www/chihopj

# 3. デプロイスクリプトを実行（自動的に最新のコードを取得します）
./deploy.sh
```

**注意**: デプロイスクリプトは自動的に以下を実行します：
- Gitから最新のコードを取得（`git fetch` + `git reset --hard`）
- Composer依存関係の更新
- アセットのビルド
- キャッシュのクリアと最適化
- データベースマイグレーション
- ファイル権限の設定

これでデプロイが完了します！

## 前提条件

### VPS側の準備

1. **必要なソフトウェアのインストール**
   ```bash
   # PHP 8.2以上（Laravel 12の要件）
   sudo apt update
   sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath
   
   # Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   
   # Node.js 18以上
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   sudo apt install -y nodejs
   
   # Git
   sudo apt install -y git
   
   # Nginx
   sudo apt install -y nginx
   ```

2. **データベースの準備**
   ```bash
   # MariaDBのインストール
   sudo apt install -y mariadb-server mariadb-client
   
   # データベースとユーザーの作成
   sudo mysql -u root -p
   ```
   ```sql
   CREATE DATABASE chihopj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'chihopj_user'@'localhost' IDENTIFIED BY '強力なパスワード';
   GRANT ALL PRIVILEGES ON chihopj.* TO 'chihopj_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **アプリケーションディレクトリの作成**
   ```bash
   sudo mkdir -p /var/www/chihopj
   sudo chown -R $USER:$USER /var/www/chihopj
   ```

## デプロイ方法

### 方法1: 手動デプロイ（推奨）

1. **VPSにSSH接続**
   ```bash
   ssh user@hiraizumin.com
   ```

2. **アプリケーションディレクトリに移動**
   ```bash
   cd /var/www/chihopj
   ```

3. **Gitリポジトリをクローン（初回のみ）**
   ```bash
   git clone https://github.com/your-username/chihopj.git .
   # または
   git clone git@github.com:your-username/chihopj.git .
   ```

4. **環境設定ファイルの作成**
   ```bash
   # .env.exampleが存在しない場合は、以下の内容で .env を作成
   nano .env  # または vi .env
   ```
   
   以下の設定を記述（最低限の設定）：
   ```env
   # アプリケーション設定
   APP_NAME="ふるぼの"
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_TIMEZONE=Asia/Tokyo
   APP_URL=https://hiraizumin.com
   
   # データベース設定
   DB_CONNECTION=mariadb
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=chihopj
   DB_USERNAME=chihopj_user
   DB_PASSWORD=your_secure_password_here
   
   # ログ設定
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   
   # セッション設定
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   
   # キャッシュ設定
   CACHE_STORE=file
   
   # キュー設定
   QUEUE_CONNECTION=sync
   
   # メール設定
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   MAIL_FROM_ADDRESS="noreply@hiraizumin.com"
   MAIL_FROM_NAME="${APP_NAME}"
   
   # ファイルストレージ設定
   FILESYSTEM_DISK=local
   ```
   
   **重要**: `APP_KEY`は後で`php artisan key:generate`で生成します。

5. **アプリケーションキーの生成**
   ```bash
   php artisan key:generate
   ```

6. **デプロイスクリプトに実行権限を付与**
   ```bash
   chmod +x deploy.sh
   ```

7. **デプロイスクリプトを実行**
   ```bash
   ./deploy.sh
   ```

### 方法2: Gitフックを使用した自動デプロイ

1. **ベアリポジトリの作成（VPS上）**
   ```bash
   cd /var/repos
   git clone --bare https://github.com/your-username/chihopj.git chihopj.git
   ```

2. **post-receiveフックの作成**
   ```bash
   cd /var/repos/chihopj.git/hooks
   nano post-receive
   ```
   
   以下の内容を記述：
   ```bash
   #!/bin/bash
   TARGET_DIR="/var/www/chihopj"
   GIT_DIR="/var/repos/chihopj.git"
   
   cd $TARGET_DIR || exit
   unset GIT_DIR
   
   git --git-dir=$GIT_DIR --work-tree=$TARGET_DIR checkout -f main
   
   cd $TARGET_DIR
   ./deploy.sh
   ```

3. **フックに実行権限を付与**
   ```bash
   chmod +x post-receive
   ```

4. **リモートリポジトリの追加（ローカル）**
   ```bash
   git remote add production user@hiraizumin.com:/var/repos/chihopj.git
   ```

5. **デプロイの実行（ローカル）**
   ```bash
   git push production main
   ```

## Nginx設定

### 設定ファイルの作成

```bash
sudo nano /etc/nginx/sites-available/chihopj
```

以下の内容を記述：

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name hiraizumin.com www.hiraizumin.com;
    
    # HTTPSにリダイレクト（Let's Encrypt使用時）
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name hiraizumin.com www.hiraizumin.com;
    
    root /var/www/chihopj/public;
    index index.php index.html;
    
    # SSL証明書の設定（Let's Encrypt使用時）
    ssl_certificate /etc/letsencrypt/live/hiraizumin.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hiraizumin.com/privkey.pem;
    
    # SSL設定
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # ログ設定
    access_log /var/log/nginx/chihopj-access.log;
    error_log /var/log/nginx/chihopj-error.log;
    
    # セキュリティヘッダー
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # ファイルサイズ制限
    client_max_body_size 20M;
    
    # Laravelのルーティング
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPMの設定
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # 静的ファイルのキャッシュ
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # 隠しファイルへのアクセスを拒否
    location ~ /\. {
        deny all;
    }
}
```

### 設定の有効化

```bash
sudo ln -s /etc/nginx/sites-available/chihopj /etc/nginx/sites-enabled/
sudo nginx -t  # 設定の構文チェック
sudo systemctl reload nginx
```

## SSL証明書の設定（Let's Encrypt）

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d hiraizumin.com -d www.hiraizumin.com
```

## トラブルシューティング

### 0. SSH接続エラー（Permission denied (publickey)）

**エラー**: `Permission denied (publickey)` が発生する

**原因**: SSH公開鍵認証が正しく設定されていない

**解決方法**:

#### 方法1: SSH公開鍵を確認・生成

```bash
# 1. 既存のSSH鍵を確認
ls -la ~/.ssh/

# 2. SSH鍵が存在しない場合、新規生成
ssh-keygen -t ed25519 -C "your_email@example.com"
# または
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# 3. 公開鍵を表示（サーバーに登録する必要がある）
cat ~/.ssh/id_ed25519.pub
# または
cat ~/.ssh/id_rsa.pub
```

#### 方法2: 公開鍵をサーバーに登録

**VPSサーバーに別の方法でアクセスできる場合（例：パスワード認証、VPS管理パネル）:**

```bash
# サーバー側で実行
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "あなたの公開鍵の内容" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**または、`ssh-copy-id`コマンドを使用（パスワード認証が有効な場合）:**

```bash
# ローカル側で実行
ssh-copy-id user@hiraizumin.com
```

#### 方法3: 正しいユーザー名を確認

```bash
# 実際のユーザー名を確認（VPS提供者に問い合わせるか、サーバー管理パネルで確認）
# 例: root, ubuntu, admin, deploy など
ssh root@hiraizumin.com
# または
ssh ubuntu@hiraizumin.com
```

#### 方法4: SSH鍵を明示的に指定

```bash
# 特定のSSH鍵を使用する場合
ssh -i ~/.ssh/id_ed25519 user@hiraizumin.com
# または
ssh -i ~/.ssh/id_rsa user@hiraizumin.com
```

#### 方法5: SSH設定ファイルを使用

```bash
# ~/.ssh/config ファイルを作成・編集
nano ~/.ssh/config
```

以下の内容を追加：

```
Host hiraizumin
    HostName hiraizumin.com
    User 実際のユーザー名
    IdentityFile ~/.ssh/id_ed25519
    # または IdentityFile ~/.ssh/id_rsa
```

その後、以下のコマンドで接続：

```bash
ssh hiraizumin
```

#### 方法6: パスワード認証が有効な場合（一時的）

```bash
# パスワード認証を使用（セキュリティ上推奨されませんが、初期設定時のみ）
ssh -o PreferredAuthentications=password -o PubkeyAuthentication=no user@hiraizumin.com
```

**注意**: パスワード認証はセキュリティリスクがあるため、公開鍵認証に切り替えることを強く推奨します。

### 1. マイグレーションエラー

**エラー**: `could not find driver (Connection: mariadb, SQL: select exists...)`

**解決方法**:
```bash
sudo apt install -y php8.2-mysql
sudo systemctl restart php8.2-fpm
```

### 2. 権限エラー

**エラー**: `Permission denied` が storage や bootstrap/cache で発生

**解決方法**:
```bash
sudo chown -R www-data:www-data /var/www/chihopj/storage
sudo chown -R www-data:www-data /var/www/chihopj/bootstrap/cache
sudo chmod -R 775 /var/www/chihopj/storage
sudo chmod -R 775 /var/www/chihopj/bootstrap/cache
```

### 3. アセットが表示されない

**解決方法**:
```bash
cd /var/www/chihopj
npm run build
php artisan storage:link
```

### 4. 500エラーが発生する

**確認事項**:
- `.env`ファイルが正しく設定されているか
- データベース接続が正常か
- ログを確認: `tail -f storage/logs/laravel.log`

## デプロイ後の確認事項

- [ ] アプリケーションが正常に表示されるか
- [ ] ログイン機能が動作するか
- [ ] データベース接続が正常か
- [ ] アセット（CSS/JS）が正しく読み込まれるか
- [ ] 画像アップロードが動作するか
- [ ] エラーログに問題がないか

## 定期メンテナンス

### ログローテーション

```bash
sudo nano /etc/logrotate.d/chihopj
```

```
/var/www/chihopj/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### バックアップ

データベースとファイルの定期バックアップを設定することを推奨します。

```bash
# データベースバックアップ
mysqldump -u chihopj_user -p chihopj > backup_$(date +%Y%m%d).sql

# ファイルバックアップ
tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/chihopj
```

## 参考リンク

- [Laravel 公式ドキュメント - デプロイメント](https://laravel.com/docs/12.x/deployment)
- [Nginx 公式ドキュメント](https://nginx.org/en/docs/)
- [Let's Encrypt 公式サイト](https://letsencrypt.org/)
