#!/bin/bash

# Couleurs pour le terminal
GREEN='\033[0;32m'
NC='\033[0m' # Pas de couleur

case "$1" in
    install)
        echo -e "${GREEN}1. Construction des images...${NC}"
        docker compose build
        
        echo -e "${GREEN}2. Installation des dépendances Backend (PHP)...${NC}"
        docker compose run --rm backend composer install
        
        echo -e "${GREEN}3. Installation des dépendances Frontend (NPM)...${NC}"
        docker compose run --rm frontend npm install
        
        echo -e "${GREEN}4. Lancement des conteneurs...${NC}"
        docker compose up -d
        
        echo -e "${GREEN}5. Configuration finale (Key & Migrations)...${NC}"
        docker compose exec backend php artisan key:generate
        docker compose exec backend php artisan migrate --seed
        
        echo -e "${GREEN}✅ Installation terminée !${NC}"
        echo -e "Accès Front-end : http://localhost:3000${NC}"
        echo -e "Accès Back-end : http://localhost:8000${NC}"
        ;;
        
    start)
        docker compose up -d
        echo -e "${GREEN}🚀 Services démarrés !${NC}"
        ;;

    stop)
        docker compose stop
        echo -e "${GREEN}🛑 Services arrêtés.${NC}"
        ;;

    repair)
        echo -e "${GREEN}🔧 Réparation en cours (Nettoyage cache + réinstallation)...${NC}"
        docker compose run --rm backend composer install
        docker compose run --rm frontend npm install
        docker compose exec backend php artisan migrate
        docker compose restart
        ;;

    update)
        echo -e "${GREEN}🔄 Mise à jour de l'environnement (post-pull)...${NC}"
        echo -e "${GREEN}1. Installation des dépendances...${NC}"
        docker compose run --rm backend composer install
        docker compose run --rm frontend npm install
        
        echo -e "${GREEN}2. Application des migrations...${NC}"
        docker compose exec backend php artisan migrate
        
        echo -e "${GREEN}✅ Environnement à jour !${NC}"
        ;;

    fresh)
        echo -e "${GREEN}⚠️ Réinitialisation complète de la base de données...${NC}"
        docker compose exec backend php artisan migrate:fresh --seed
        echo -e "${GREEN}✅ Base de données réinitialisée et synchronisée !${NC}"
        ;;

    *)
        echo "Usage: ./sygma.sh {install|start|stop|update|fresh|repair}"
        exit 1
        ;;
esac