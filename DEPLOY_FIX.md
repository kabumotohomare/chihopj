# マイグレーションエラー修正手順

## エラー内容
```
could not find driver (Connection: mariadb, SQL: select exists...)
```

## 原因
PHPのPDO MySQL/MariaDB拡張機能（`pdo_mysql`）がインストールされていない、または有効になっていません。

## 解決方法

### Ubuntu/Debian系の場合

```bash
# PHP拡張機能をインストール
sudo apt-get update
sudo apt-get install php8.2-mysql  # PHP 8.2の場合
# または
sudo apt-get install php8.5-mysql  # PHP 8.5の場合

# PHP-FPMを再起動
sudo systemctl restart php8.2-fpm  # または php8.5-fpm
# または
sudo systemctl restart php-fpm
```

### CentOS/RHEL系の場合

```bash
# PHP拡張機能をインストール
sudo yum install php-mysqlnd
# または
sudo dnf install php-mysqlnd

# PHP-FPMを再起動
sudo systemctl restart php-fpm
```

### Docker/Sail環境の場合

`Dockerfile`または`docker-compose.yml`でPHP拡張機能をインストールする必要があります。

```dockerfile
# Dockerfileに追加
RUN docker-php-ext-install pdo_mysql
```

### 確認方法

```bash
# PHP拡張機能がインストールされているか確認
php -m | grep pdo_mysql

# または
php -i | grep pdo_mysql
```

### 本番環境での実行手順

1. PHP拡張機能をインストール
2. Webサーバー（PHP-FPM、Apache、Nginx）を再起動
3. マイグレーションを再実行

```bash
php artisan migrate --force
```
