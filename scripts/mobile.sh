#!/bin/bash
set -e

PROJET_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
NGROK_API="http://localhost:4040/api/tunnels"

# Arrêter ngrok si déjà en cours
pkill ngrok 2>/dev/null || true
sleep 1

# Démarrer ngrok
echo "Démarrage de ngrok..."
ngrok start --all --config "$HOME/.config/ngrok/ngrok.yml" --config "$PROJET_ROOT/ngrok.yml" > /dev/null 2>&1 &

# Attendre que l'API ngrok soit prête (max 15s)
echo "Attente de l'API ngrok..."
for i in $(seq 1 15); do
  if curl -s "$NGROK_API" > /dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 15 ]; then
    echo "Erreur : ngrok n'a pas démarré. Vérifie ton authtoken (ngrok config add-authtoken <token>)."
    exit 1
  fi
  sleep 1
done

# Récupérer l'URL HTTPS du frontend
FRONTEND_URL=$(curl -s "$NGROK_API" | jq -r '.tunnels[] | select(.name == "frontend") | .public_url')

if [ -z "$FRONTEND_URL" ]; then
  echo "Erreur : impossible de récupérer l'URL ngrok."
  exit 1
fi

NGROK_DOMAIN=$(echo "$FRONTEND_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')

# Mise à jour du backend .env
ENV_FILE="$PROJET_ROOT/backend/.env"
echo "Mise à jour du backend avec l'URL : $FRONTEND_URL"
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=${FRONTEND_URL}/auth/google/callback|" "$ENV_FILE"
sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" "$ENV_FILE"
sed -i "s|^APP_URL=.*|APP_URL=${FRONTEND_URL}|" "$ENV_FILE"
sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${NGROK_DOMAIN},localhost:3000,127.0.0.1:3000|" "$ENV_FILE"
sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=.${NGROK_DOMAIN}|" "$ENV_FILE"
sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" "$ENV_FILE"
sed -i "s|^SESSION_SAME_SITE=.*|SESSION_SAME_SITE=none|" "$ENV_FILE"

# Redémarrage du backend pour recharger .env
echo "Redémarrage du backend..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" restart backend > /dev/null 2>&1

# Build du frontend et démarrage en mode preview (production)
echo "Build du frontend (peut prendre ~30s)..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" -f "$PROJET_ROOT/docker-compose.mobile.yml" up -d --no-deps --force-recreate frontend > /dev/null 2>&1

# Attendre que le frontend soit prêt (max 120s)
echo "Attente du frontend..."
for i in $(seq 1 120); do
  if curl -s http://localhost:3000 > /dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 120 ]; then
    echo "Erreur : le frontend n'a pas démarré en 120s."
    exit 1
  fi
  sleep 1
done

echo ""
echo "======================================"
echo "  Ouvre sur ton téléphone :"
echo "  $FRONTEND_URL"
echo "======================================"
echo ""
qrencode -t ANSIUTF8 "$FRONTEND_URL"
echo ""
echo "  (Ctrl+C ou 'make mobile-stop' pour revenir en mode local)"
wait
