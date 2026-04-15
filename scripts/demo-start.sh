#!/bin/bash
set -e

PROJET_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$PROJET_ROOT/backend/.env"
NGROK_API="http://localhost:4040/api/tunnels"
NGROK_FRONTEND="https://bullpen-unafraid-mutual.ngrok-free.dev"

echo "Activation du mode démo mobile (Google Auth via ngrok)..."

# Mise à jour des URLs dans .env
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=${NGROK_FRONTEND}/auth/google/callback|" "$ENV_FILE"
sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${NGROK_FRONTEND}|" "$ENV_FILE"

# Redémarrage du backend pour recharger .env
echo "Redémarrage du backend..."
docker compose -f "$PROJET_ROOT/docker-compose.yml" restart backend > /dev/null 2>&1

# Attendre que le backend soit prêt (max 30s)
echo "Attente du backend..."
for i in $(seq 1 30); do
  if curl -s "http://localhost:8000" > /dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 30 ]; then
    echo "Erreur : le backend n'a pas redémarré."
    exit 1
  fi
  sleep 1
done

# Démarrer ngrok
pkill ngrok 2>/dev/null || true
sleep 1
echo "Démarrage de ngrok..."
ngrok start --all --config "$HOME/.config/ngrok/ngrok.yml" --config "$PROJET_ROOT/ngrok.yml" > /dev/null 2>&1 &

# Attendre l'API ngrok (max 15s)
for i in $(seq 1 15); do
  if curl -s "$NGROK_API" > /dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 15 ]; then
    echo "Erreur : ngrok n'a pas démarré."
    exit 1
  fi
  sleep 1
done

echo ""
echo "======================================"
echo "  Mode démo activé !"
echo "  $NGROK_FRONTEND"
echo "======================================"
echo ""
qrencode -t ANSIUTF8 "$NGROK_FRONTEND"
echo ""
echo "  (Ctrl+C ou 'make demo-stop' pour revenir en mode local)"
wait
