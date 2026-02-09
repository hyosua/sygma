FROM node:20-alpine

WORKDIR /app

# On copie les fichiers de dépendances
COPY package*.json ./

# Installation propre (plus rapide et déterministe)
RUN npm install

# Copie du reste des fichiers
COPY . .

# On expose le port
EXPOSE 3000

# On utilise CMD avec le format tableau (meilleure gestion des signaux d'arrêt)
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "3000"]