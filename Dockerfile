FROM php:8.2-apache

# Instalar extensiones requeridas
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# Habilitar mod_rewrite
RUN a t2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html/