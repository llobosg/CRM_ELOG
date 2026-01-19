# ========================
# STAGE 1: Composer
# ========================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# =========================
# STAGE 2: Apache + PHP
# =========================
FROM php:8.2-apache

# Dependencias PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apache rewrite (NO tocar MPM)
RUN a2enmod rewrite

# Código
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
