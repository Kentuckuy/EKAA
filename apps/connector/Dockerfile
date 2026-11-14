FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    libsodium-dev \
    && docker-php-ext-install pdo pdo_pgsql zip sodium

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json ./
COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
