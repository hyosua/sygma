#!/bin/bash
set -e

PROJET_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$PROJET_ROOT/backend/.env"

echo "Retour en mode local (localhost)..."

# Restauration des URLs localhost dans .env
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback|" "$ENV_FILE"
sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=http://localhost:3000|" "$ENV_FILE"

# Arrêt de ngrok
pkill ngrok 2>/dev/null || true

# Redémarrage du backend pour recharger .env
echo "Redémarrage du backend..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" restart backend > /dev/null 2>&1

echo ""
echo "======================================"
echo "  Mode local restauré."
echo "  Frontend : http://localhost:3000"
echo "  Backend  : http://localhost:8000"
echo "======================================"
