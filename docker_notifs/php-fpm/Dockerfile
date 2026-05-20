FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev zip autoconf build-base git linux-headers

RUN docker-php-ext-install pdo_pgsql bcmath sockets

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/notifs.dev

RUN chown -R www-data:www-data /var/www/notifs.dev

USER www-data