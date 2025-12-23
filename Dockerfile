# -------- Builder stage (Composer) --------
FROM composer:2 AS vendor

WORKDIR /app

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl zip

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts


# -------- Runtime stage (Apache + PHP) --------
FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=prod \
    APP_DEBUG=0

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install intl opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && printf '\n<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/*



WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor from builder stage
COPY --from=vendor /app/vendor ./vendor

# Permissions for Symfony
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

EXPOSE 80
