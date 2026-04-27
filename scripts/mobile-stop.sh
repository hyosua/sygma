#!/bin/bash
set -e

PROJET_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$PROJET_ROOT/backend/.env"

pkill ngrok 2>/dev/null || true
echo "ngrok arrêté."

# Restauration des URLs localhost dans .env
echo "Retour en mode local (localhost)..."
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback|" "$ENV_FILE"
sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=http://localhost:3000|" "$ENV_FILE"
sed -i "s|^APP_URL=.*|APP_URL=http://localhost:8000|" "$ENV_FILE"
sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=null|" "$ENV_FILE"
sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=false|" "$ENV_FILE"
sed -i "s|^SESSION_SAME_SITE=.*|SESSION_SAME_SITE=lax|" "$ENV_FILE"

# Redémarrage du backend pour recharger .env
echo "Redémarrage du backend..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" restart backend > /dev/null 2>&1

# Retour au frontend en mode dev
echo "Retour au frontend en mode dev..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" up -d --no-deps --force-recreate frontend > /dev/null 2>&1

echo ""
echo "======================================"
echo "  Mode local restauré."
echo "======================================"
