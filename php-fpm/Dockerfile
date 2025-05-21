FROM php:8.3.21-fpm-alpine3.20

# Install common PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    && docker-php-ext-install pdo_pgsql pgsql pdo_mysql

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Optional: Install Composer if you manage dependencies inside the container
COPY --from=composer/composer:latest /usr/bin/composer /usr/bin/composer
