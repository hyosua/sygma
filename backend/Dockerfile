FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    git \
    curl \
    bash \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    icu-libs \
    nodejs \
    npm

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure gd && \
    docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cache layer : dépendances PHP
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction

# Cache layer : dépendances Node
COPY package*.json ./
RUN npm ci --prefer-offline

# Source complète
COPY . .

RUN composer dump-autoload --optimize

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
