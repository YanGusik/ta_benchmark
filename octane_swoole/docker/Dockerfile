FROM phpswoole/swoole:6.0-php8.4-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql opcache pcntl

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app
