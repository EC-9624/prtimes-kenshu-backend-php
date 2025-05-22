FROM php:8.3.21-fpm-alpine3.20

RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    && docker-php-ext-install pdo_pgsql pgsql pdo_mysql

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

COPY --from=composer/composer:latest /usr/bin/composer /usr/bin/composer
