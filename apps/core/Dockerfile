FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json ./
# composer install will be executed in full dev/build pipeline

COPY . .

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
