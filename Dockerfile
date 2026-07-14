FROM dunglas/frankenphp:php8.4-bookwork

RUN install-php-extensions \
    ctype curl dom fileinfo filter hash mbstring openssl pcre pdo pdo_mysql session tokenizer xml intl bcmath zip

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev

# Storage/cache harus writable oleh user FrankenPHP
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# TIDAK ADA ENV SERVER_NAME — akan di-set runtime
# EXPOSE tidak wajib, tapi Railway pakai untuk healthcheck
EXPOSE 8080

# Runtime: SERVER_NAME di-resolve dari $PORT yang Railway inject
CMD ["sh", "-c", "SERVER_NAME=:${PORT:-8080} exec frankenphp php-server -r public/"]