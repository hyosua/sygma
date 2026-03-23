FROM php:8.3-cli-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    git curl bash \
    libpng-dev oniguruma-dev libxml2-dev \
    zip unzip \
    postgresql-dev libzip-dev \
    icu-dev icu-libs \
    nodejs npm

RUN docker-php-ext-configure intl && \
    docker-php-ext-install \
    pdo_pgsql mbstring exif pcntl bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} appuser 2>/dev/null || true && \
    adduser -u ${UID} -G appuser -s /bin/sh -D appuser 2>/dev/null || true

WORKDIR /var/www/html

# Pré-crée vendor/ avec le bon propriétaire pour que le volume nommé Docker
# hérite de ces droits à l'initialisation (premier démarrage sans volume existant).
# Sans ça, Docker crée le répertoire en root et composer install échoue
# quand le conteneur tourne avec un utilisateur non-root (user: UID:GID).
RUN mkdir -p /var/www/html/vendor /var/www/html/node_modules && \
    chown ${UID}:${GID} /var/www/html /var/www/html/vendor /var/www/html/node_modules

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]