# Sygma - Gestion de Présence Numérique 🚀

## 📋 Table des matières
1. [💻 Configuration de l'environnement](#1-configuration-de-lenvironnement)
2. [📥 Mise en place du dépôt (Clonage)](#2--mise-en-place-du-dépôt-clonage)
3. [⚡️ Premier Setup (Installation)](#3--premier-setup-installation)
4. [🛠 Session de travail quotidienne](#4--session-de-travail-quotidienne)
5. [🌿 Procédure Git & Collaboration](#5--procédure-git--collaboration)
6. [🌐 Accès & Commandes](#6--accès--commandes)
7. [🛠 Gestion des Librairies & Scripts](#7--gestion-des-librairies--scripts)

---

## 1. Configuration de l'environnement

- **Docker Desktop** : [Installez-le](https://www.docker.com/products/docker-desktop) et assurez-vous qu'il tourne.  
- **VS Code** : Installez l'extension officielle WSL de Microsoft.  
- **Connexion** : Cliquez sur le bouton bleu "><" en bas à gauche de VS Code → **Connect to WSL**.  
  *(Si Ubuntu n'est pas installé, VS Code vous proposera de le faire automatiquement).*

---

## 2. 📥 Mise en place du dépôt (Clonage)

**⚠️ IMPORTANT :** Ne clonez pas le projet dans vos dossiers Windows habituels (Bureau, Documents). Pour que Docker soit rapide, le code doit être dans Linux.

1. Une fois que VS Code affiche **WSL: Ubuntu** en bas à gauche, ouvrez le terminal intégré (`Ctrl + ù`)
![Fenêtre Wsl](screenshots/wsl-window.png)   
2. Créez un dossier pour vos projets :
   ```bash
   cd ~
   mkdir -p projects && cd projects
   ```

3. Clonez le dépôt :
   ```bash
   git clone https://github.com/hyosua/sygma.git
   cd sygma
   ```

4. Lancez VS Code dans ce dossier :
   ```bash
   code .
   ```

---

## 3. ⚡️ Premier Setup (Installation)

Une fois le projet ouvert dans VS Code (via WSL) :

### Configuration d'environnement

```bash
cp backend/.env.example backend/.env
```
(Demandez les accès pour les variables env si je ne vous les ai pas déjà donnés).

### Lancement du projet

Vous avez deux méthodes pour installer les dépendances et démarrer le projet :

#### Option A : La méthode rapide (Recommandé)
Utilisez le script automatisé qui s'occupe de tout (build, install, migrations, seed) :

```bash
chmod +x sygma.sh
./sygma.sh install
```

#### Option B : La méthode manuelle
Si vous préférez exécuter les commandes étape par étape :

1. **Installation des dépendances** (une seule fois) :
```bash
docker compose build
docker compose run --rm backend composer install
docker compose run --rm frontend npm install
```

2. **Démarrage des serveurs** :
```bash
docker compose up -d
```

3. **Initialisation de la BDD** :
```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```
---

## 4. 🛠 Session de travail quotidienne

Plus besoin de tout réinstaller ! À chaque nouvelle session :

- **Récupérer le travail** : `git pull origin main`
- **Démarrer les serveurs** : `docker compose up -d`
- **Travailler** : Les modifications de code sont visibles en temps réel.
- **Quitter** : `docker compose stop` (libère la RAM de votre PC).

---

## 5. 🌿 Procédure Git & Collaboration

Pour un historique propre et éviter les conflits :

### Nouvelle tâche

Créez toujours une branche :
```bash
git checkout -b "feat/ma-fonctionnalite"
```

### Avant de Push

```bash
git add .
git commit -m "Message clair et concis"
git pull origin main  # pour fusionner le travail récent des autres
```

### Envoyer

```bash
git push origin feat/ma-fonctionnalite
```

---

## 6. 🌐 Accès & Commandes

| Service | URL / Port |
|---------|-----------|
| Front-end (React) | http://localhost:3000 |
| Back-end (API) | http://localhost:8000 |
| PostgreSQL | Port 5432 |

---
## 7. 🛠 Gestion des Librairies & Scripts

Pour ajouter une librairie (ex: un package Composer ou un module NPM), vous ne devez pas l'installer sur votre Windows. Vous devez demander au conteneur de le faire.

### 1. La méthode "Raccourci" (Script Sygma)
J'ai créé un script `./sygma.sh` pour vous simplifier la vie. 
Ne pas oublier de lui donner les permissions d'exécution avec `chmod +x sygma.sh`.
*Pour l'utiliser sous Windows, faites-le depuis votre terminal WSL ou Git Bash.*

* **Installation complète** : `./sygma.sh install` (Build + Install + Migrate)
* **Démarrer le projet** : `./sygma.sh start`
* **Arrêter le projet** : `./sygma.sh stop`
* **Réparer / Réinstaller** : `./sygma.sh repair` (Réinstalle les dépendances)

### 2. Installer de nouveaux packages
Si vous avez besoin d'ajouter une dépendance spécifique :

**Pour le Backend (PHP) :**
```bash
docker compose exec backend composer require nom-du-package
```

**Pour le Frontend (React) :**

```bash
docker compose exec frontend npm install nom-du-package
```

*Note : Une fois installé, le fichier package.json ou composer.json sera mis à jour sur votre ordinateur automatiquement grâce aux volumes Docker.*

---

## 💡 Astuces de secours

- **Logs en direct** : `docker compose logs -f`
- **Réinitialiser un conteneur** : `docker compose restart backend`
- **Erreur de permissions** : `docker compose exec backend chown -R www-data:www-data storage`