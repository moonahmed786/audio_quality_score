FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        git \
        libsqlite3-dev \
        libzip-dev \
        libicu-dev \
        unzip \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip intl \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer update --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer update --optimize-autoloader --no-interaction --prefer-dist

RUN chmod +x docker/entrypoint.sh \
    && mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
