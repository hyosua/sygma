# Guide de Contribution - Sygma

Pour que le projet reste propre et qu'on s'y retrouve, voici quelques règles du jeu.

## 📋 En bref
1. [🌿 Tes Branches](#-tes-branches)
2. [✅ Qualité & Pull Request](#-qualité--pull-request)
3. [🐳 Le Workflow Docker](#-le-workflow-docker--la-règle-dor)
    - [🚀 Commandes à connaître](#-commandes-à-connaître)
    - [⛔ Les Interdits](#-les-interdits)

---

## 🌿 Tes Branches

Une idée, une branche ! Pars toujours de `main` et utilise le bon préfixe :
- `feat/` : pour une nouvelle fonctionnalité.
- `fix/` : pour corriger un bug.
- `docs/`, `refactor/`, `test/` : pour le reste.

```bash
# 1. Assure-toi que main est à jour
git pull origin main

# 2. Crée ta branche
git checkout -b "feat/ma-super-idee"
```

---

## ✅ Qualité & Pull Request

1.  **Teste ton code** avant de `push` pour être sûr de ne rien casser :
    ```bash
    docker compose exec backend php artisan test
    ```
2.  **Fais une PR claire** vers `main` : un bon titre et une description simple de "quoi" et "pourquoi".

---

## 🐳 Le Workflow Docker : La Règle d'Or

C'est super simple :
- **Ton PC = Ton éditeur de code (VS Code).**
- **Docker = Ton serveur (là où les commandes s'exécutent).**

Tu écris ton code sur ton PC, et il apparaît magiquement dans Docker. Tu ne touches à rien d'autre !

### 🚀 Commandes à connaître

Toutes les commandes se lancent via `sygma ...` (version courte) ou `docker compose exec ...` (version longue).

#### Backend (PHP/Laravel)
- **Installer un package :**
  ```bash
  docker compose exec backend composer require <nom-du-package>
  ```
    _ou via le script `sygma` :_
  ```bash
  sygma composer require <nom-du-package>
  ```- **Commandes Artisan :**
  ```bash
  docker compose exec backend php artisan <ta-commande>
  ```
    _ou via le script `sygma` :_
  ```bash
  sygma artisan <ta-commande>
  ```
#### Frontend (React)
- **Installer un package :**
  ```bash
  docker compose exec frontend npm install <nom-du-package>
  ```
    _ou via le script `sygma` :_
  ```bash
  sygma npm install <nom-du-package>
  ```- **Lancer un script (lint, etc.) :**
  ```bash
  docker compose exec frontend npm run <nom-du-script>
  ```
    _ou via le script `sygma` :_
  ```bash
  sygma npm run <nom-du-script>
  ```
### ⛔ Les Interdits

À ne **JAMAIS** faire :
1.  **Lancer un serveur de dev à la main** (`npm run dev`, `artisan serve`). (Docker le fait déjà avec `docker compose up`).
2.  **Installer PHP, Composer,Node ou une autre dépendance sur ton PC.** (Inutile, tout est déjà dans Docker).
3.  **Modifier du code en dehors de ton éditeur** (pas de `docker exec` pour éditer des fichiers, c'est risqué et pas nécessaire).
