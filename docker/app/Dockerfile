# Local dev runtime: Laravel Sail's PHP 8.4 base + Composer, plus the Postgres
# PDO driver (not bundled in the base image).
FROM laravelsail/php84-composer:latest

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
