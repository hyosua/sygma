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

echo ""
echo "======================================"
echo "  Ouvre sur ton téléphone :"
echo "  $FRONTEND_URL"
echo "======================================"
echo ""
qrencode -t ANSIUTF8 "$FRONTEND_URL"
echo ""
echo "  (Ctrl+C pour arrêter ngrok)"
wait
