FROM php:8.3.21-fpm-alpine3.20

RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    autoconf \ 
    build-base \
    && docker-php-ext-install pdo_pgsql pgsql pdo_mysql

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

RUN chown www-data:www-data /var/log/ && chmod 755 /var/log/

RUN mkdir -p /var/tmp/php_upload_tmp \
    && chown www-data:www-data /var/tmp/php_upload_tmp \
    && chmod 755 /var/tmp/php_upload_tmp

WORKDIR /var/www/html

COPY --from=composer/composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-ansi --no-progress \
    && composer dump-autoload --optimize

COPY . /var/www/html

COPY php-fpm/php.ini /usr/local/etc/php/conf.d/custom.ini
