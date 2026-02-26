#!/bin/bash

# PHP PDO MySQL拡張機能インストールスクリプト
# 本番環境で実行してください

echo "PHP PDO MySQL拡張機能のインストールを開始します..."

# PHPバージョンを確認
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "検出されたPHPバージョン: $PHP_VERSION"

# OSを検出
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
else
    echo "OSを検出できませんでした。手動でインストールしてください。"
    exit 1
fi

echo "検出されたOS: $OS"

# Ubuntu/Debian系
if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
    echo "Ubuntu/Debian系OSを検出しました。"
    
    # パッケージリストを更新
    sudo apt-get update
    
    # PHP拡張機能をインストール
    if [ "$PHP_VERSION" = "8.5" ]; then
        sudo apt-get install -y php8.5-mysql
        PHP_FPM_SERVICE="php8.5-fpm"
    elif [ "$PHP_VERSION" = "8.2" ]; then
        sudo apt-get install -y php8.2-mysql
        PHP_FPM_SERVICE="php8.2-fpm"
    elif [ "$PHP_VERSION" = "8.1" ]; then
        sudo apt-get install -y php8.1-mysql
        PHP_FPM_SERVICE="php8.1-fpm"
    else
        sudo apt-get install -y php-mysql
        PHP_FPM_SERVICE="php-fpm"
    fi
    
    # PHP-FPMを再起動
    if systemctl is-active --quiet $PHP_FPM_SERVICE 2>/dev/null; then
        echo "PHP-FPMを再起動します..."
        sudo systemctl restart $PHP_FPM_SERVICE
    fi

# CentOS/RHEL系
elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ] || [ "$OS" = "fedora" ]; then
    echo "CentOS/RHEL系OSを検出しました。"
    
    if command -v dnf &> /dev/null; then
        sudo dnf install -y php-mysqlnd
    else
        sudo yum install -y php-mysqlnd
    fi
    
    # PHP-FPMを再起動
    if systemctl is-active --quiet php-fpm 2>/dev/null; then
        echo "PHP-FPMを再起動します..."
        sudo systemctl restart php-fpm
    fi

else
    echo "サポートされていないOSです。手動でインストールしてください。"
    exit 1
fi

# インストール確認
echo ""
echo "インストール確認中..."
if php -m | grep -q pdo_mysql; then
    echo "✅ PDO MySQL拡張機能が正常にインストールされました！"
    php -m | grep pdo_mysql
else
    echo "❌ PDO MySQL拡張機能のインストールに失敗しました。"
    exit 1
fi

echo ""
echo "マイグレーションを実行してください:"
echo "php artisan migrate --force"
