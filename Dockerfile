# === STAGE 1: Build y dependencias ===
# Usa una imagen específica de Composer (más rápida y limpia para la instalación)
FROM composer:2.7 as build

# Establecer directorio de trabajo en el contenedor de construcción
WORKDIR /app

# Copiar archivos esenciales (composer.json y composer.lock)
# ASUME que 'composer.lock' YA ESTÁ EN LA RAÍZ DE TU REPOSITORIO
COPY composer.json composer.lock ./

# Instalar dependencias de Composer (sin scripts ni dependencias de desarrollo)
RUN composer install --ignore-platform-reqs --no-dev --no-scripts --prefer-dist --optimize-autoloader

# === STAGE 2: Imagen final con PHP-FPM y Nginx ===
# Usa la imagen PHP-FPM ligera con Alpine
FROM php:8.2-fpm-alpine

# Instalar Nginx y las extensiones de PHP necesarias (PostgreSQL, zip, Git)
RUN apk update && apk add --no-cache nginx git \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    # Limpiar caché de paquetes
    && rm -rf /var/cache/apk/*

# Copiar los archivos de la aplicación
WORKDIR /var/www/html
COPY . .

# Copiar las dependencias instaladas de Composer desde el stage de "build"
COPY --from=build /app/vendor /var/www/html/vendor

# Establecer permisos de carpetas para que el servidor web pueda escribir (Necesario para 'storage' de Laravel)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar archivo de configuración de Nginx
# ¡ASEGÚRATE DE QUE 'nginx.conf' ESTÉ EN LA RAÍZ!
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Exponer el puerto del servidor web
EXPOSE 80

# Comando de inicio: correr PHP-FPM y Nginx simultáneamente en el foreground
CMD sh -c "php-fpm && nginx -g 'daemon off;'"