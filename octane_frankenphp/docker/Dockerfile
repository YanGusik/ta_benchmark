FROM php:8.5-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev unzip curl \
    && docker-php-ext-install pdo_pgsql pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
