# ==========================================================================
# Produktions-Image (ADR-034: nginx + php-fpm, KEIN Octane; ADR-041:
# Dockerfile statt Compose, solange es nur einen Prozess gibt).
#
# Coolify: Build-Pack "Dockerfile", Kontext = Repo-Wurzel, Pfad = Dockerfile.
# Pre-Deploy-Command (ADR-015/036) — ohne `--database=pgsql_owner` schlaegt es
# fehl, weil der Standard auf `dormed_staff` zeigt, eine Rolle, die zu dem
# Zeitpunkt erst angelegt wird:
#
#     php artisan migrate --force --database=pgsql_owner
#
# Postgres und S3 sind eigene Coolify-Ressourcen, nicht Teil dieses Images.
#
# Die Server-Konfiguration liegt in eigenen Dateien daneben — nginx.conf,
# php.ini, supervisord.conf, entrypoint.sh. Jede traegt ihre Begruendung
# selbst und laesst sich einzeln pruefen.
# ==========================================================================

# ---- 1. PHP-Abhaengigkeiten ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# `--no-scripts`: Artisan laeuft hier noch ohne Anwendungscode.
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ---- 2. Frontend bauen (Inertia + Svelte, ADR-039) ----
#
# Setzt bewusst auf der vendor-Stufe auf und nicht auf `node:alpine`: das
# Wayfinder-Vite-Plugin ruft waehrend des Builds `php artisan wayfinder:generate`
# auf, um die typisierten Routenhelfer zu erzeugen. In einem reinen Node-Image
# scheitert das mit `php: not found` — und zwar als Build-Abbruch, nicht als
# Warnung.
FROM vendor AS assets
RUN apk add --no-cache nodejs npm
RUN npm ci
RUN npm run build

# ---- 3. Laufzeit ----
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache nginx supervisor postgresql-client icu-libs libzip libpng libjpeg-turbo freetype \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql gd intl zip bcmath opcache \
 && apk del .build-deps

COPY php.ini          /usr/local/etc/php/conf.d/99-dormed.ini
COPY nginx.conf       /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisord.conf
COPY entrypoint.sh    /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
 && rm -rf /var/www/html/database/database.sqlite

EXPOSE 80
ENTRYPOINT ["entrypoint"]
