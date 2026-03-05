FROM php:8.3-fpm

# システム依存パッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer のインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js 20 LTS のインストール（Viteビルド用）
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP設定
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY php-local.ini /usr/local/etc/php/conf.d/php-local.ini

WORKDIR /var/www/html

# www-data ユーザーの UID/GID をホストに合わせる
ARG UID=1000
ARG GID=1000
RUN groupmod -g ${GID} www-data \
    && usermod -u ${UID} -g ${GID} www-data

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
