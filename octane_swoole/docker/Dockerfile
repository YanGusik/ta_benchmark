FROM phpswoole/swoole:php8.5-zts

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app
