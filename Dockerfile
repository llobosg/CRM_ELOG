FROM php:8.2-apache

WORKDIR /var/www/html

# Dependencias del sistema + PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 🔥 ELIMINACIÓN FORZADA DE MPMs CONFLICTIVOS
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.* \
          /etc/apache2/mods-available/mpm_event.* \
          /etc/apache2/mods-available/mpm_worker.*

# 🔒 Forzar solo prefork
RUN a2enmod mpm_prefork rewrite

# Copiar proyecto
COPY . .

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
