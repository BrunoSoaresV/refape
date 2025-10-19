# syntax=docker/dockerfile:1

FROM php:8.3-cli

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_PORT=8000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY laravel-app/ ./

RUN composer install --no-interaction --prefer-dist \
    && chown -R www-data:www-data /var/www/html

COPY docker/laravel/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER www-data

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
