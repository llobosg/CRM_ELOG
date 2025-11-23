# Usa una imagen oficial de PHP con Apache (ligera y compatible)
FROM php:8.2-apache

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Habilita módulos de Apache y PHP necesarios
RUN docker-php-ext-install pdo pdo_mysql

# Copia todo el proyecto
COPY . /var/www/html/

# Instala dependencias de Composer
RUN if [ -f composer.json ]; then \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    composer install --no-dev --optimize-autoloader; \
    fi

# Asegura permisos
RUN chown -R www-data:www-data /var/www/html/

# Puerto expuesto
EXPOSE 80

# Apache ya está configurado para servir desde /var/www/html