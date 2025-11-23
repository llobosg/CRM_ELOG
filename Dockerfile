# Usa PHP 8.2 con Apache
FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Habilitar módulos PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copiar la app
COPY . /var/www/html/

# Instalar Composer y dependencias
RUN if [ -f composer.json ]; then \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    composer install --no-dev --optimize-autoloader; \
    fi

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html/

# Puerto estándar para Railway
EXPOSE 8080

# Apache escucha en el puerto 80 por defecto, pero Railway espera 8080
# Así que redirigimos el puerto 8080 al 80 usando una regla de proxy inverso
# Pero es más simple: usar PHP con servidor embebido en 8080

# 👇 Cambia a servidor PHP embebido en lugar de Apache
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]