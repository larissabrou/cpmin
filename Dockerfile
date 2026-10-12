# ---------------------------------------------------------------
# Étape 1 : build des assets front (Vite)
# ---------------------------------------------------------------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------
# Étape 2 : application Laravel (PHP-FPM + Nginx, port 8080)
# https://serversideup.net/open-source/docker-php/
# ---------------------------------------------------------------
FROM serversideup/php:8.2-fpm-nginx

# Au démarrage du conteneur : migrations, storage:link, caches config/routes/vues
ENV AUTORUN_ENABLED=true \
    PHP_OPCACHE_ENABLE=1

USER root
WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
