FROM php:8.2-apache-bookworm

# Instalar dependencias del sistema
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
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

# Corrección MPM
RUN a2dismod mpm_event mpm_worker && \
    a2enmod mpm_prefork rewrite

# Copiar proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

EXPOSE 80