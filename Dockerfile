<<<<<<< HEAD
FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-interaction --prefer-dist

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
=======
# === STAGE 1: Build y dependencias ===
FROM composer:2.7 as build

# Establecer directorio de trabajo en el contenedor de construcción
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-dev --no-scripts --prefer-dist --optimize-autoloader

# === STAGE 2: Imagen final con PHP-FPM y Nginx ===
FROM php:8.2-fpm-alpine

# Instalar Nginx y extensiones necesarias
RUN apk update && apk add --no-cache nginx git \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/cache/apk/*

# Copiar archivos de la aplicación y dependencias
WORKDIR /var/www/html
COPY . .
COPY --from=build /app/vendor /var/www/html/vendor

# COPIAR EL ARCHIVO DE CONFIGURACIÓN DE NGINX
COPY nginx.conf /etc/nginx/conf.d/default.conf

# === AHORA INTEGRAMOS LOS COMANDOS DE LARAVEL AQUÍ ===
# IMPORTANTE: Esto requiere que las VARIABLES DE ENTORNO (DB_HOST, etc.) 
# YA ESTÉN CONFIGURADAS EN RENDER para que las migraciones funcionen.

# 1. Generar la clave de la aplicación (Necesario para el inicio)
RUN php /var/www/html/artisan key:generate --force

# 2. Ejecutar Migraciones de la Base de Datos
RUN php /var/www/html/artisan migrate --force

# 3. Limpiar caches de configuración y vistas
RUN php /var/www/html/artisan optimize:clear

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto
EXPOSE 80

# Comando de inicio: correr PHP-FPM y Nginx
CMD sh -c "php-fpm && nginx -g 'daemon off;'"
#>>>>>>> 889bc793f40190ce676ad41cfa40f11b8712dbef
