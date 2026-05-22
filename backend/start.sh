#!/bin/sh
set -e

echo "==> PORT=$PORT APP_ENV=$APP_ENV"
echo "==> Migrations..."
if [ "${SEED_ON_DEPLOY}" = "true" ]; then
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
fi
echo "==> Démarrage serveur sur 0.0.0.0:${PORT:-8000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
