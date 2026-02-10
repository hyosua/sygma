# Sygma - Gestion de Présence Numérique 🚀

Hello l'équipe ! Bienvenue sur le dépôt du projet Sygma. 

Ce projet utilise une architecture moderne avec un **Backend Laravel 11**, un **Frontend React 18** et une base de données **PostgreSQL**. Tout est orchestré avec **Docker** pour que nous ayons tous exactement le même environnement, même sur Windows.

---

## 🛠 Prérequis (Windows)

Avant de commencer, installez ces deux outils indispensables :
1.  **[Docker Desktop pour Windows](https://www.docker.com/products/docker-desktop/)**  
    *⚠️ Lors de l'installation, assurez-vous de cocher l'option "Use WSL2 based engine" pour de meilleures performances.*
2.  **[Git for Windows](https://gitforwindows.org/)** (qui installe "Git Bash").

---

## ⚡️ Installation Rapide (First Setup)

Ouvrez un terminal (**Git Bash** ou **PowerShell**) dans le dossier où vous voulez mettre le projet :

### 1. Cloner le projet
```bash
git clone https://github.com/VOTRE_NOM/NOM_DU_PROJET.git
cd Sygma
```

### 2. Configurer l'environnement (.env)
Il faut créer le fichier de configuration pour le backend.
*   **Sur PowerShell / Git Bash :**
    ```bash
    cp backend/.env.example backend/.env
    ```
*   **Sur l'invite de commande (CMD) :**
    ```cmd
    copy backend\.env.example backend\.env
    ```

### 3. Lancer Docker
Assurez-vous que **Docker Desktop est bien lancé** dans votre barre des tâches, puis :
```bash
docker compose up -d --build
```

### 4. Initialiser le Backend (Laravel)
On installe les dépendances PHP et on prépare la base de données :
```bash
# Installation des packages
docker compose exec backend composer install

# Génération de la clé de sécurité
docker compose exec backend php artisan key:generate

# Création des tables
docker compose exec backend php artisan migrate
```

### 5. Initialiser le Frontend (React)
On installe les dépendances JavaScript :
```bash
docker compose exec frontend npm install
```

---

## 🌐 Accès à l'application

Une fois que tout est lancé, vous pouvez accéder aux services ici :

*   **Frontend (React)** : [http://localhost:3000](http://localhost:3000)
*   **Backend API (Laravel)** : [http://localhost:8000](http://localhost:8000)

---

## 💡 Commandes Utiles (Windows)

*   **Arrêter le projet** : `docker compose down`
*   **Relancer le projet** : `docker compose up -d`
*   **Voir ce qui se passe (Logs)** : `docker compose logs -f`
*   **Accéder au terminal du backend** : `docker compose exec backend bash`

---

## 🤝 Quelques règles pour collaborer

1.  **Git Pull** : Avant de commencer à bosser, faites toujours un `git pull origin main`.
2.  **Migrations** : Si vous voyez de nouveaux fichiers dans `backend/database/migrations`, lancez `docker compose exec backend php artisan migrate`.
3.  **Docker** : Si vous avez une erreur bizarre après un pull, tentez un `docker compose up -d --build`.

Si vous avez un souci de configuration sur Windows, envoyez-moi un message.