FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 🔥 Corrección clave: deshabilitar MPMs ANTES de habilitar prefork
RUN a2dismod mpm_event mpm_worker && \
    a2enmod mpm_prefork rewrite

# Copiar solo los archivos necesarios
COPY . /var/www/html/

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

EXPOSE 80