FROM phpswoole/swoole:5.1-php8.3-alpine

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql opcache pcntl

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app
