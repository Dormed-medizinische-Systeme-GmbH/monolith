# syntax=docker/dockerfile:1
#
# Test / staging image for the Coolify deployment (compose.prod.yaml).
# Runtime is `php artisan serve` — single process, adequate for a handful of
# testers. For real traffic switch this stage to FrankenPHP or php-fpm + nginx.

# --- PHP dependencies (no dev, no scripts) ---------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# One retry + source fallback (default prefer-install=auto) to ride out transient
# registry / GitHub API hiccups during the build.
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --no-progress \
    || composer install --no-dev --no-scripts --no-autoloader --no-interaction --no-progress

# --- Frontend assets ------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
RUN npm run build

# --- Runtime ------------------------------------------------------------------
FROM php:8.4-cli AS runtime

# libpq-dev stays: it also provides libpq5 that pdo_pgsql links against at runtime.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

USER www-data
EXPOSE 8000

# Migrations run on every container start (single replica). package:discover
# rebuilds the package manifest against the baked-in vendor tree.
CMD ["sh", "-c", "php artisan package:discover --ansi && php artisan migrate --force && exec php artisan serve --host=0.0.0.0 --port=8000"]
