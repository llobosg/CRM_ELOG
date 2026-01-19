FROM php:8.2-cli-bookworm

# Instalar extensiones necesarias
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        default-mysql-client \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd mysqli pdo pdo_mysql zip \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

# Copiar proyecto
COPY . /app

# Permisos
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app

WORKDIR /app

# Exponer puerto
EXPOSE 8000

# Iniciar servidor PHP
CMD ["php", "-S", "0.0.0.0:8000"]