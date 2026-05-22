SYGMA_UID := $(shell id -u)
SYGMA_GID := $(shell id -g)
export SYGMA_UID
export SYGMA_GID

DOCKER_USER := -u $(SYGMA_UID):$(SYGMA_GID)

GREEN := \033[0;32m
NC    := \033[0m

.PHONY: install start stop restart mobile mobile-stop demo-start demo-stop repair update fresh test lint-check lint-fix composer artisan npm-back npm-front deploy help %

install:
	@echo "$(GREEN)1. Construction des images...$(NC)"
	docker compose build
	@echo "$(GREEN)2. Installation des dependances Backend...$(NC)"
	docker compose run --rm $(DOCKER_USER) backend composer install
	docker compose run --rm $(DOCKER_USER) backend npm install
	@echo "$(GREEN)3. Installation des dependances Frontend...$(NC)"
	docker compose run --rm $(DOCKER_USER) frontend npm install
	@echo "$(GREEN)4. Lancement des conteneurs...$(NC)"
	docker compose up -d
	@echo "$(GREEN)5. Configuration finale (Key & Migrations)...$(NC)"
	docker compose exec $(DOCKER_USER) backend php artisan key:generate
	docker compose exec $(DOCKER_USER) backend php artisan migrate --seed
	@echo "$(GREEN)Installation terminee !$(NC)"
	@echo "Front-end : http://localhost:3000"
	@echo "Back-end  : http://localhost:8000"
	@echo "Adminer   : http://localhost:8080"
	git config core.hooksPath .githooks

start:
	docker compose up -d
	@echo "$(GREEN)Services demarres !$(NC)"
	@echo "Front-end : http://localhost:3000"
	@echo "Back-end  : http://localhost:8000"
	@echo "Adminer   : http://localhost:8080"

stop:
	docker compose stop
	@echo "$(GREEN)Services arretes.$(NC)"

restart:
	docker compose stop
	docker compose up -d
	@echo "$(GREEN)Services redemarres !$(NC)"
	@echo "Front-end : http://localhost:3000"
	@echo "Back-end  : http://localhost:8000"
	@echo "Adminer   : http://localhost:8080"

mobile:
	@bash scripts/mobile.sh

mobile-stop:
	@bash scripts/mobile-stop.sh

demo-start:
	@bash scripts/demo-start.sh

demo-stop:
	@bash scripts/demo-stop.sh

repair:
	@echo "$(GREEN)Reparation en cours...$(NC)"
	docker compose run --rm $(DOCKER_USER) backend composer install
	docker compose run --rm $(DOCKER_USER) backend npm install
	docker compose run --rm $(DOCKER_USER) frontend npm install
	docker compose run --rm $(DOCKER_USER) backend php artisan migrate
	docker compose restart
	@echo "Front-end : http://localhost:3000"
	@echo "Back-end  : http://localhost:8000"
	@echo "Adminer   : http://localhost:8080"

update:
	@echo "$(GREEN)Mise a jour de l'environnement...$(NC)"
	docker compose run --rm $(DOCKER_USER) backend composer install
	docker compose run --rm $(DOCKER_USER) backend npm install
	docker compose run --rm $(DOCKER_USER) frontend npm install
	docker compose run --rm $(DOCKER_USER) backend php artisan migrate
	@echo "$(GREEN)Environnement a jour !$(NC)"
	@echo "Front-end : http://localhost:3000"
	@echo "Back-end  : http://localhost:8000"
	@echo "Adminer   : http://localhost:8080"

fresh:
	docker compose exec $(DOCKER_USER) backend php artisan migrate:fresh --seed
	@echo "$(GREEN)Base de donnees reinitialisee !$(NC)"

test:
	@docker compose exec db psql -U sygma -tc "SELECT 1 FROM pg_database WHERE datname = 'sygma_test'" | grep -q 1 || \
		docker compose exec db psql -U sygma -c "CREATE DATABASE sygma_test WITH OWNER sygma;"
	docker compose exec $(DOCKER_USER) backend php artisan test
	@echo "$(GREEN)Tests termines !$(NC)"

lint-check:
	docker compose exec $(DOCKER_USER) backend ./vendor/bin/pint --test
	docker compose exec $(DOCKER_USER) backend ./vendor/bin/phpcs --standard=phpcs.xml
	docker compose exec $(DOCKER_USER) frontend npm run lint

lint-fix:
	docker compose exec $(DOCKER_USER) backend ./vendor/bin/pint
	docker compose exec $(DOCKER_USER) backend ./vendor/bin/phpcbf --standard=phpcs.xml || true
	docker compose exec $(DOCKER_USER) frontend npm run lint:fix

composer:
	docker compose exec $(DOCKER_USER) backend composer $(filter-out $@,$(MAKECMDGOALS))

artisan:
	docker compose exec $(DOCKER_USER) backend php artisan $(filter-out $@,$(MAKECMDGOALS))

npm-back:
	docker compose exec $(DOCKER_USER) backend npm $(filter-out $@,$(MAKECMDGOALS))

npm-front:
	docker compose exec $(DOCKER_USER) frontend npm $(filter-out $@,$(MAKECMDGOALS))

deploy:
	cd backend && railway up --service backend

%:
	@:

help:
	@echo "Usage: make <commande> [ARGS=\"...\"]"
	@echo ""
	@echo "  install      Premier lancement complet"
	@echo "  start        Demarrer les conteneurs"
	@echo "  stop         Arreter les conteneurs"
	@echo "  restart      Redemarrer les conteneurs
	@echo "  mobile       Activer le mode mobile via ngrok (HTTPS)"
	@echo "  mobile-stop  Revenir en mode desktop (localhost)"
	@echo "  demo-start   Mode demo mobile avec Google Auth (ngrok)"
	@echo "  demo-stop    Revenir en mode local apres demo"
	@echo "  update       Mettre a jour apres un git pull"
	@echo "  repair       Reinstaller les dependances et redemarrer"
	@echo "  fresh        Reinitialiser la base de donnees"
	@echo "  test         Lancer les tests (base sygma_test isolee)"
	@echo "  lint-check   Verifier le lint (PHP + JS)"
	@echo "  lint-fix     Corriger le lint automatiquement (PHP + JS)"
	@echo ""
	@echo "  make composer require package"
	@echo "  make artisan  migrate --seed"
	@echo "  make npm-back run build"
	@echo "  make npm-front run build"
	@echo ""
	@echo "  deploy       Deployer le backend sur Railway"
