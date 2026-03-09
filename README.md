# Sygma - Gestion de Présence Numérique

**Sygma** est une application web dédiée à la dématérialisation de l'émargement. Elle permet aux étudiants de s'émarger via QR Code, aux enseignants de piloter les séances, et aux gestionnaires de suivre les absences.

## 👥 Auteurs
- Emmanuelle Nsossani
- Yahaya Coulibaly
- Hyosua Colleter

## 📋 Table des matières
1. [💻 Configuration de l'environnement](#1-configuration-de-lenvironnement)
2. [📥 Mise en place du dépôt (Clonage)](#2--mise-en-place-du-dépôt-clonage)
3. [⚡️ Premier Setup (Installation)](#3-premier-setup-installation)
4. [🛠 Session de travail quotidienne](#4--session-de-travail-quotidienne)
5. [🌿 Procédure Git & Collaboration](#5--procédure-git--collaboration)
6. [🌐 Accès & Commandes](#6--accès--commandes)
7. [📦 Référence des commandes Make](#7--référence-des-commandes-make)
8. [📊 Visualisation & Requêtes BDD](#8--visualisation--requêtes-bdd)
9. [🧪 Tests & Données de démo](#9--tests--données-de-démo)

---

## 1. Configuration de l'environnement

### 🪟 Si vous êtes sur Windows (Recommandé)
- **Docker Desktop** : [Installez-le](https://www.docker.com/products/docker-desktop) et activez le moteur WSL2.
- **VS Code** : Installez l'extension officielle **WSL** de Microsoft.
- **Connexion** : Cliquez sur le bouton bleu "><" en bas à gauche de VS Code → **Connect to WSL**.

### 🐧 Si vous êtes sur Linux (Natif) ou WSL sans Docker Desktop
- **Docker** : Installez Docker et Docker Compose V2.
- **Permissions** : Par défaut, Docker nécessite `sudo`. Pour éviter cela, ajoutez votre utilisateur au groupe `docker` :
  ```bash
  sudo usermod -aG docker $USER
  ```
  **⚠️ IMPORTANT** : Vous devez fermer et réouvrir votre terminal (ou redémarrer WSL avec `wsl --shutdown` dans un PowerShell Windows) pour que cela soit pris en compte.

---

## 2. 📥 Mise en place du dépôt (Clonage)

### 🪟 Sur Windows (via WSL)
**⚠️ IMPORTANT :** Ne clonez pas le projet dans vos dossiers Windows habituels (C:\Users...). Pour que Docker soit rapide, le code doit résider dans le système de fichiers Linux.

1. Ouvrez VS Code connecté à WSL (Ubuntu).
2. Dans le terminal (`Ctrl + ù`), créez votre dossier de travail :
   ```bash
   cd ~
   mkdir -p projects && cd projects
   git clone https://github.com/hyosua/sygma.git
   cd sygma
   code .
   ```

### 🐧 Sur Linux
Clonez simplement le dépôt dans votre dossier de projets habituel :
```bash
git clone https://github.com/hyosua/sygma.git
cd sygma
code .
```

---

## 3. Premier Setup (Installation)

### ⚠️ Problème de permissions (Sudo) ?
Si vous devez taper `sudo` avant chaque commande Docker ou si vous avez des erreurs "Permission Denied", lancez ces commandes :

1. **Récupérer la propriété des fichiers** :
   ```bash
   sudo chown -R $USER:$USER .
   ```
2. **S'assurer que make est installé** :
   ```bash
   which make || sudo apt install make
   ```

---

### Configuration d'environnement

```bash
cp backend/.env.example backend/.env
```
(Demandez les accès pour les variables env si je ne vous les ai pas déjà donnés).

### Lancement du projet

Vous avez deux méthodes pour installer les dépendances et démarrer le projet :

#### Option A : La méthode rapide (Recommandé)
Utilisez le Makefile qui s'occupe de tout (build, install, migrations, seed) :

```bash
make install
```

#### Option B : La méthode manuelle
Si vous préférez exécuter les commandes étape par étape :

1. **Installation des dépendances** (une seule fois) :
```bash
docker compose build
docker compose run --rm -u "$(id -u):$(id -g)" backend composer install
docker compose run --rm -u "$(id -u):$(id -g)" backend npm install
docker compose run --rm -u "$(id -u):$(id -g)" frontend npm install
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
- **Mettre à jour** : `make update` (Installe les nouvelles dépendances et applique les migrations)
- **Démarrer les serveurs** : `make start`
- **Travailler** : Les modifications de code sont visibles en temps réel.
- **Quitter** : `make stop` (libère la RAM de votre PC).

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
| Adminer (BDD) | http://localhost:8080 |
| PostgreSQL | Port 5432 |

---

## 7. 📦 Référence des commandes Make

Le projet utilise un `Makefile` pour simplifier les commandes courantes. Depuis la racine du projet :

### Cycle de vie du projet

**Installer tout (build + dépendances + migrations + seed) :**
```bash
make install
```

**Démarrer les serveurs :**
```bash
make start
```

**Arrêter les serveurs :**
```bash
make stop
```

**Mettre à jour après un `git pull` (nouvelles dépendances + migrations) :**
```bash
make update
```

**Réinitialiser la BDD et repeupler :**
```bash
make fresh
```

**Réparer les volumes / permissions :**
```bash
make repair
```

### Backend (PHP/Laravel)

**Installer un package Composer :**
```bash
make composer ARGS="require <package>"
```

**Créer un modèle Artisan :**
```bash
make artisan ARGS="make:model <Nom>"
```

**Lancer les migrations :**
```bash
make artisan ARGS="migrate"
```

### Frontend (React)

**Installer un package frontend :**
```bash
make npm-front ARGS="install <package>"
```

**Installer un package backend (Node) :**
```bash
make npm-back ARGS="install <package>"
```

*Note : Ces commandes s'exécutent directement à l'intérieur des conteneurs Docker. Pour ajouter une librairie, demandez toujours au conteneur de le faire — n'installez pas directement sur votre machine.*

---

## 8. 📊 Visualisation & Requêtes BDD


### Adminer (Simple - Sans installation)
C'est une interface web déjà prête.
1. Ouvrez [http://localhost:8080](http://localhost:8080) dans votre navigateur.
2. Connectez-vous avec :
   - **Système** : `PostgreSQL`
   - **Serveur** : `db`
   - **Utilisateur** : `sygma`
   - **Mot de passe** : `sygma_pass`
   - **Base de données** : `sygma`
---

## 💡 Astuces de secours

- **Logs en direct** : `docker compose logs -f`
- **Réinitialiser un conteneur** : `docker compose restart backend`
- **Récupérer les droits sur tout le projet** : `sudo chown -R $USER:$USER .`

---

## 9. 🧪 Tests & Données de démo

### Peupler la base de données (Seeding)
Pour obtenir un jeu de données de test complet et interconnecté (utilisateurs avec rôles variés, groupes, cours, inscriptions, séances et enregistrements de présence simulés), vous avez deux options :

**Option 1 (Recommandée - via make) :**
Utilisez la commande simplifiée :
```bash
make fresh
```

**Option 2 (Manuelle - via Docker Compose) :**
Exécutez la commande Docker complète :
```bash
docker compose exec backend php artisan migrate:fresh --seed
```

Ces commandes sont les options recommandées pour une mise en place rapide d'un environnement de développement avec des données significatives et prêtes à l'emploi.

**⚠️ IMPORTANT :** Ces commandes vont **supprimer toutes les données existantes** de votre base de données avant de la reconstruire et de la remplir avec les données de démonstration. Utilisez-les avec précaution !

Après avoir exécuté l'une de ces commandes, vous aurez un utilisateur "gestionnaire" avec les identifiants :
- Email : `admin@sygma.com`
- Mot de passe : `password`

---

### Créer une séance active pour tester l'émargement

Pour tester le workflow QR Code (professeur → étudiant) sans passer par tinker, utilisez la commande dédiée. Elle affiche l'URL `/enseignant/session/{id}` à ouvrir directement.

**Créer une nouvelle séance active (durée 2h par défaut) :**
```bash
docker compose exec -u 1000:1000 backend php artisan test:seance-active
```

**Remettre à jour la dernière séance existante (évite de créer des doublons) :**
```bash
docker compose exec -u 1000:1000 backend php artisan test:seance-active --reset
```

**Durée personnalisée (en minutes) :**
```bash
docker compose exec -u 1000:1000 backend php artisan test:seance-active --duree=30
```

---

### Exécuter les tests
Les tests permettent de vérifier que les fonctionnalités (comme la gestion des groupes) fonctionnent correctement.

**Lancer tous les tests :**
```bash
make artisan ARGS="test"
```

**Lancer uniquement les tests liés aux groupes :**
```bash
make artisan ARGS="test --filter GroupManagementTest"
```
