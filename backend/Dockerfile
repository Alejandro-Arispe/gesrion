# === STAGE 1: Build y dependencias ===
FROM composer:2.7 as build

# Establecer directorio de trabajo en el contenedor de construcción
WORKDIR /app

# Copiar archivos esenciales (composer.json y composer.lock)
COPY composer.json composer.lock ./

# Instalar dependencias de Composer (incluyendo dev si quieres)
RUN composer install --ignore-platform-reqs --no-dev --no-scripts --prefer-dist --optimize-autoloader

# === STAGE 2: Imagen final con PHP-FPM y Nginx ===
FROM php:8.2-fpm-alpine

# Instalar extensiones de PHP necesarias (PostgreSQL, zip, etc.)
# Usamos 'alpine' para una imagen más pequeña
RUN apk add --no-cache nginx git \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Copiar los archivos de la aplicación
WORKDIR /var/www/html
COPY . .

# Copiar las dependencias instaladas de Composer desde el stage de "build"
COPY --from=build /app/vendor /var/www/html/vendor

# Configurar Laravel (Generar Key, Storage link, etc.)
# Nota: Esto se debe hacer ANTES de que el servicio comience en Render, a menudo con un "Build Command".
# Para este ejemplo, lo haremos en el Dockerfile, pero es mejor usar el Build Command de Render.
# RUN php artisan key:generate --no-interaction --force

# Limpiar caches
RUN php artisan optimize:clear

# Establecer permisos de carpetas para que el servidor web pueda escribir
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar archivo de configuración de Nginx
# ¡DEBES CREAR EL ARCHIVO 'nginx.conf' EN LA RAÍZ!
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Exponer el puerto de Nginx
EXPOSE 80

# Comando de inicio: correr PHP-FPM y Nginx
# Correr Nginx en el foreground y PHP-FPM como un proceso paralelo.
CMD sh -c "nginx && php-fpm"