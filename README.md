# Sygma - Gestion de Présence Numérique

**Sygma** est une application web dédiée à la dématérialisation de l'émargement. Elle permet aux étudiants de s'émarger via QR Code, aux enseignants de piloter les séances, et aux gestionnaires de suivre les absences.

## Auteurs
- Emmanuelle Nsossani
- Yahaya Coulibaly
- Hyosua Colleter

## Table des matières
1. [Setup Windows (WSL)](#1-setup-windows-wsl)
2. [Setup Mac](#2-setup-mac)
3. [Setup Linux](#3-setup-linux)
4. [Installation (commun)](#4-installation-commun)
5. [Session de travail quotidienne](#5-session-de-travail-quotidienne)
6. [Procédure Git & Collaboration](#6-procédure-git--collaboration)
7. [Accès & Commandes](#7-accès--commandes)
8. [Référence des commandes Make](#8-référence-des-commandes-make)
9. [Visualisation de la BDD](#9-visualisation-de-la-bdd)
10. [Tests & Données de démo](#10-tests--données-de-démo)
11. [Problèmes courants](#11-problèmes-courants)

---

## 1. Setup Windows (WSL)

### Prérequis
- **Docker Desktop** : [Installez-le](https://www.docker.com/products/docker-desktop) et activez le moteur WSL2.
- **VS Code** : Installez l'extension officielle **WSL** de Microsoft.
- **Connexion WSL** : Cliquez sur le bouton bleu "><" en bas à gauche de VS Code → **Connect to WSL**.

### Clonage

> **Important :** Ne clonez pas le projet dans vos dossiers Windows (`C:\Users\...`). Le code doit résider dans le système de fichiers Linux pour que Docker soit performant.

```bash
cd ~
mkdir -p projects && cd projects
git clone https://github.com/hyosua/sygma.git
cd sygma
code .
```

Passez ensuite à la section [4. Installation](#4-installation-commun).

---

## 2. Setup Mac

### Prérequis
- **Docker Desktop for Mac** : [Installez-le](https://www.docker.com/products/docker-desktop).
- **make** : si absent, installez-le via Homebrew :
  ```bash
  brew install make
  ```

### Clonage

```bash
git clone https://github.com/hyosua/sygma.git
cd sygma
code .
```

Passez ensuite à la section [4. Installation](#4-installation-commun).

---

## 3. Setup Linux

### Prérequis
- **Docker** et **Docker Compose V2** : installez-les via votre gestionnaire de paquets.

### Clonage

```bash
git clone https://github.com/hyosua/sygma.git
cd sygma
code .
```

---

## 4. Installation (commun)

### 1. Copier les fichiers d'environnement
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```
Demandez les variables d'environnement si elles ne vous ont pas été communiquées.

### 2. Lancer l'installation
```bash
(which make || sudo apt install make) && make install
```
Cette commande s'occupe de tout : build des images, installation des dépendances, génération de la clé applicative, migrations et seed.

### Méthode manuelle (si nécessaire)
Si vous ne pouvez pas utiliser `make`, exécutez les étapes à la main :

```bash
# Build et dépendances
docker compose build
docker compose run --rm -u "$(id -u):$(id -g)" backend composer install
docker compose run --rm -u "$(id -u):$(id -g)" backend npm install
docker compose run --rm -u "$(id -u):$(id -g)" frontend npm install

# Démarrage
docker compose up -d

# Initialisation de la BDD
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```

---

## 5. Session de travail quotidienne

```bash
git pull origin main   # Récupérer le travail des autres
make update            # Installer les nouvelles dépendances et appliquer les migrations
make start             # Démarrer les serveurs
```

Les modifications de code sont visibles en temps réel. Pour arrêter : `make stop`.

---

## 6. Procédure Git & Collaboration

Le workflow complet (branches, rebase, PRs, conventions) est documenté dans **[CONTRIBUTING.md](./CONTRIBUTING.md)**.

---

## 7. Accès & Commandes

| Service | URL / Port |
|---------|-----------|
| Front-end (React) | http://localhost:3000 |
| Back-end (API) | http://localhost:8000 |
| Adminer (BDD) | http://localhost:8080 |
| PostgreSQL | Port 5432 |

---

## 8. Référence des commandes Make

### Cycle de vie du projet

| Commande | Description |
|----------|-------------|
| `make install` | Premier lancement complet (build + dépendances + migrations + seed) |
| `make start` | Démarrer les serveurs |
| `make stop` | Arrêter les serveurs |
| `make restart` | Redémarrer les serveurs |
| `make update` | Mettre à jour après un `git pull` (dépendances + migrations) |
| `make fresh` | Réinitialiser la BDD et repeupler |
| `make repair` | Réinstaller les dépendances et redémarrer les conteneurs |
| `make test` | Lancer les tests (base `sygma_test` isolée) |
| `make mobile` | Activer le mode mobile via ngrok (HTTPS) |
| `make mobile-stop` | Revenir en mode desktop (localhost) |
| `make lint-check` | Vérifier le lint (PHP + JS) |
| `make lint-fix` | Corriger le lint automatiquement |

### Backend (PHP/Laravel)

```bash
make composer require <package>   # Installer un package Composer
make artisan make:model <Nom>     # Créer un modèle
make artisan migrate              # Lancer les migrations
```

### Frontend (React)

```bash
make npm-front install <package>  # Installer un package frontend
make npm-back install <package>   # Installer un package Node (backend)
```

Ces commandes s'exécutent à l'intérieur des conteneurs Docker. N'installez pas de paquets directement sur votre machine.

---

## 9. Visualisation de la BDD

Adminer est disponible sur [http://localhost:8080](http://localhost:8080). Connectez-vous avec :

| Champ | Valeur |
|-------|--------|
| Système | `PostgreSQL` |
| Serveur | `db` |
| Utilisateur | `sygma` |
| Mot de passe | `sygma_pass` |
| Base de données | `sygma` |

---

## 10. Tests & Données de démo

### Peupler la base de données

Pour obtenir un jeu de données complet (utilisateurs, groupes, cours, séances, présences) :

```bash
make fresh
```

Ou manuellement :
```bash
docker compose exec backend php artisan migrate:fresh --seed
```

> **Ces commandes suppriment toutes les données existantes.** À utiliser uniquement en développement.

Après le seed, un compte gestionnaire est disponible :
- Email : `admin@sygma.com`
- Mot de passe : `password`

---

### Créer une séance active pour tester l'émargement

```bash
# Créer une nouvelle séance active (2h par défaut)
docker compose exec -u 1000:1000 backend php artisan test:seance-active

# Réinitialiser la dernière séance existante (évite les doublons)
docker compose exec -u 1000:1000 backend php artisan test:seance-active --reset

# Durée personnalisée (en minutes)
docker compose exec -u 1000:1000 backend php artisan test:seance-active --duree=30
```

La commande affiche l'URL `/enseignant/session/{id}` à ouvrir directement.

---

### Exécuter les tests

```bash
make test
```

Pour filtrer par suite :
```bash
make artisan test --filter EmargementServiceTest
make artisan test --filter SeanceControllerTest
make artisan test --filter SessionEmargementTest
make artisan test --filter GroupManagementTest
```

> `make artisan test` ne crée pas la base `sygma_test` automatiquement. Utiliser `make test` pour le premier lancement.

Pour l'architecture des tests (isolation `sygma_test`, couverture), voir [docs/doc_technique.md](./docs/doc_technique.md#tests).

---

## 11. Problèmes courants

### `docker compose` nécessite `sudo` (Linux uniquement)

Par défaut sur Linux, Docker nécessite `sudo`. Pour éviter cela, ajoutez votre utilisateur au groupe `docker` :

```bash
sudo usermod -aG docker $USER
```

Fermez et réouvrez votre terminal pour que cela soit pris en compte.

### Erreurs "Permission Denied" sur les fichiers

1. Récupérer la propriété des fichiers :
   ```bash
   sudo chown -R $USER:$USER .
   ```
2. Vérifier que `make` est installé :
   ```bash
   which make || sudo apt install make   # Linux
   which make || brew install make       # Mac
   ```
3. Réinstaller les dépendances et redémarrer :
   ```bash
   make repair
   ```

### Autres commandes utiles

```bash
docker compose logs -f              # Voir les logs en direct
docker compose restart backend      # Redémarrer un conteneur
```
