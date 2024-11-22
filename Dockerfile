FROM php:8.3-fpm-alpine

WORKDIR /var/www/app

COPY --from=composer /usr/bin/composer /usr/bin/composer