FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        icu-data-full \
        libpq-dev \
        libzip-dev \
        unzip \
        postgresql-client \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql zip opcache gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

WORKDIR /var/www/app

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
