FROM php:8.2-apache-bookworm

# Instalar dependencias del sistema
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        zip \
        unzip \
        default-mysql-client \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd mysqli pdo pdo_mysql zip \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

# 🔥 Solución real: deshabilitar MPMs y habilitar prefork EN EL MISMO COMANDO
RUN echo "LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so" > /etc/apache2/mods-enabled/mpm_prefork.load && \
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_worker.load && \
    a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

EXPOSE 80