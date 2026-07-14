FROM dunglas/frankenphp:php8.4-bookworm

RUN install-php-extensions \
    ctype curl dom fileinfo filter hash mbstring openssl pcre pdo pdo_mysql session tokenizer xml intl bcmath zip

WORKDIR /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Copy application source
COPY . .
RUN composer dump-autoload --optimize --no-dev

ENV SERVER_NAME=:${PORT:-8080}
EXPOSE 8080

CMD ["frankenphp", "php-server", "-r", "public/"]
