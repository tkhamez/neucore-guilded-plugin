FROM php:8.3-alpine
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
