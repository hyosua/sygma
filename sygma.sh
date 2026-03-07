#!/bin/bash

# Couleurs pour le terminal
GREEN='\033[0;32m'
NC='\033[0m' # Pas de couleur

# Détection de l'UID et GID pour éviter les problèmes de droits root
# On utilise 1000:1000 par défaut si on n'arrive pas à les détecter (rare sur Linux/macOS)
CURRENT_UID=$(id -u 2>/dev/null || echo 1000)
CURRENT_GID=$(id -g 2>/dev/null || echo 1000)
DOCKER_USER_OPT="-u $CURRENT_UID:$CURRENT_GID"

# Exporté pour que docker-compose puisse lire ${SYGMA_UID} et ${SYGMA_GID} dans le yaml
# On n'utilise pas UID/GID car UID est readonly en bash
export SYGMA_UID="$CURRENT_UID"
export SYGMA_GID="$CURRENT_GID"

case "$1" in
    install)
        echo -e "${GREEN}1. Construction des images...${NC}"
        docker compose build
        
        echo -e "${GREEN}2. Installation des dépendances Backend...${NC}"
        docker compose run --rm $DOCKER_USER_OPT backend composer install
        docker compose run --rm $DOCKER_USER_OPT backend npm install
        
        echo -e "${GREEN}3. Installation des dépendances Frontend (NPM)...${NC}"
        docker compose run --rm $DOCKER_USER_OPT frontend npm install
        
        echo -e "${GREEN}4. Lancement des conteneurs...${NC}"
        docker compose up -d
        
        echo -e "${GREEN}5. Configuration finale (Key & Migrations)...${NC}"
        docker compose exec $DOCKER_USER_OPT backend php artisan key:generate
        docker compose exec $DOCKER_USER_OPT backend php artisan migrate --seed
        
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
        docker compose run --rm $DOCKER_USER_OPT backend composer install
        docker compose run --rm $DOCKER_USER_OPT backend npm install
        docker compose run --rm $DOCKER_USER_OPT frontend npm install
        docker compose exec $DOCKER_USER_OPT backend php artisan migrate
        docker compose restart
        ;;

    update)
        echo -e "${GREEN}🔄 Mise à jour de l'environnement (post-pull)...${NC}"
        echo -e "${GREEN}1. Installation des dépendances...${NC}"
        docker compose run --rm $DOCKER_USER_OPT backend composer install
        docker compose run --rm $DOCKER_USER_OPT backend npm install
        docker compose run --rm $DOCKER_USER_OPT frontend npm install
        
        echo -e "${GREEN}2. Application des migrations...${NC}"
        docker compose exec $DOCKER_USER_OPT backend php artisan migrate
        
        echo -e "${GREEN}✅ Environnement à jour !${NC}"
        ;;

    fresh)
        echo -e "${GREEN}⚠️ Réinitialisation complète de la base de données...${NC}"
        docker compose exec $DOCKER_USER_OPT backend php artisan migrate:fresh --seed
        echo -e "${GREEN}✅ Base de données réinitialisée et synchronisée !${NC}"
        ;;

    setup)
        echo -e "${GREEN}🔧 Configuration de l'alias 'sygma'...${NC}"
        SCRIPT_PATH=$(realpath "$0")
        SHELL_CONFIG=""

        if [ -n "$($SHELL -c 'echo $ZSH_VERSION')" ]; then
            SHELL_CONFIG="$HOME/.zshrc"
        elif [ -n "$($SHELL -c 'echo $BASH_VERSION')" ]; then
            SHELL_CONFIG="$HOME/.bashrc"
        fi

        if [ -n "$SHELL_CONFIG" ]; then
            # Supprimer l'ancien alias s'il existe et ajouter le nouveau
            sed -i '/alias sygma=/d' "$SHELL_CONFIG"
            echo "alias sygma='$SCRIPT_PATH'" >> "$SHELL_CONFIG"
            echo -e "${GREEN}✅ Alias 'sygma' ajouté à $SHELL_CONFIG${NC}"
            echo -e "👉 Tapez ${GREEN}source $SHELL_CONFIG${NC} ou redémarrez votre terminal pour l'utiliser."
        else
            echo -e "❌ Impossible de détecter votre configuration shell (bash/zsh)."
        fi
        ;;

    composer)
        shift
        docker compose exec $DOCKER_USER_OPT backend composer "$@"
        ;;

    npm)
        shift
        if [ "$1" == "backend" ] || [ "$1" == "back" ]; then
            shift
            docker compose exec $DOCKER_USER_OPT backend npm "$@"
        else
            # Si le premier argument est "frontend", on le shift, sinon on garde tout pour le frontend par défaut
            if [ "$1" == "frontend" ] || [ "$1" == "front" ]; then shift; fi
            docker compose exec $DOCKER_USER_OPT frontend npm "$@"
        fi
        ;;

    artisan)
        shift
        docker compose exec $DOCKER_USER_OPT backend php artisan "$@"
        ;;

    *)
        echo "Usage: sygma {install|start|stop|update|fresh|repair|setup|composer|npm|artisan}"
        exit 1
        ;;
esac