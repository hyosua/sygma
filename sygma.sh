#!/bin/bash

# Couleurs pour le terminal
GREEN='\033[0;32m'
NC='\033[0m' # No Color

echo -e "${GREEN}🐘 Assistant Sygma v1.0${NC}"

case "$1" in
    start)
        docker compose up -d
        ;;
    stop)
        docker compose stop
        ;;
    install)
        echo "📦 Installation des dépendances..."
        docker compose exec backend composer install
        docker compose exec frontend npm install
        ;;
    migrate)
        echo "🗄 Migration de la base de données..."
        docker compose exec backend php artisan migrate
        ;;
    clear)
        echo "🧹 Nettoyage du cache..."
        docker compose exec backend php artisan config:clear
        docker compose exec backend php artisan cache:clear
        ;;
    *)
        echo "Usage: ./sygma.sh {start|stop|install|migrate|clear}"
        exit 1
        ;;
esac