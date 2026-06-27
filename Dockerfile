# ── MBG Traceability — Laravel API (PHP 8.2 + MySQL + MongoDB) ──────────────
# Single-container image for Fly.io. Apache serves Laravel's /public.
FROM php:8.2-apache

# System libs needed to build the PHP extensions + mysql client for imports
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libssl-dev pkg-config default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# SQL side: pdo_mysql + zip. NoSQL side: mongodb (this replaces the
# hand-installed php_mongodb.dll you use locally on XAMPP).
RUN docker-php-ext-install pdo_mysql zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Point Apache at Laravel's public/ and allow .htaccess rewrites
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel \
    && a2enmod rewrite

WORKDIR /var/www/html

# Install PHP deps first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# App source
COPY . .
# --no-scripts: don't run `artisan package:discover` at build time (no env yet);
# Laravel rebuilds the package manifest on first boot.
RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
