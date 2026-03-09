# Guide de Contribution - Sygma

Pour que le projet reste propre et qu'on s'y retrouve, voici quelques règles du jeu.

## En bref
1. [Config Git (une seule fois)](#config-git-une-seule-fois)
2. [Workflow au quotidien](#workflow-au-quotidien)
3. [Tes Branches](#tes-branches)
4. [Qualite & Pull Request](#qualite--pull-request)
5. [Le Workflow Docker](#le-workflow-docker--la-regle-dor)
    - [Commandes a connaitre](#commandes-a-connaitre)
    - [Les Interdits](#les-interdits)

---

## Config Git (une seule fois)

Configure Git une fois pour toutes pour éviter les merges parasites :

```bash
git config --global pull.rebase true   # pull = rebase, pas merge
git config --global rebase.autoStash true  # stash auto si fichiers non commités
```

---

## Workflow au quotidien

### Démarrer une nouvelle fonctionnalité

```bash
git checkout main
git pull                        # récupère main à jour (rebase, pas merge)
git checkout -b feat/ma-feature
```

### Avant de faire une PR (ta branche est en retard sur main)

```bash
git fetch origin
git rebase origin/main          # rejoue tes commits par-dessus main à jour
# En cas de conflit : résous > git add . > git rebase --continue
git push --force-with-lease     # force push safe (nécessaire après rebase)
```

> **Pourquoi `rebase` plutôt que `merge` ?**
> `merge` crée un commit parasite "Merge branch..." qui pollue l'historique.
> `rebase` rejoue tes commits proprement au bout de main. L'historique reste linéaire.

### Les règles d'or

| Interdit | A la place |
|---|---|
| `git pull` (merge par défaut) | `git pull` (après config rebase, c'est bon) |
| `git merge main` depuis ta branche | `git rebase origin/main` |
| Push direct sur `main` | PR obligatoire |
| `git push --force` | `git push --force-with-lease` (plus sûr) |

---

## Tes Branches

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

## Qualite & Pull Request

1.  **Teste ton code** avant de `push` pour être sûr de ne rien casser :
    ```bash
    make artisan ARGS="test"
    ```
2.  **Fais une PR claire** vers `main` : un bon titre et une description simple de "quoi" et "pourquoi".

---

## Le Workflow Docker : La Regle d'Or

C'est super simple :
- **Ton PC = Ton éditeur de code (VS Code).**
- **Docker = Ton serveur (là où les commandes s'exécutent).**

Tu écris ton code sur ton PC, et il apparaît magiquement dans Docker. Tu ne touches à rien d'autre !

### Commandes a connaitre

Toutes les commandes se lancent via `make ...` (version courte) ou `docker compose exec ...` (version longue).

#### Backend (PHP/Laravel)
- **Installer un package :**
  ```bash
  make composer ARGS="require <nom-du-package>"
  ```

- **Commandes Artisan :**
  ```bash
  make artisan ARGS="<ta-commande>"
  ```

#### Frontend (React)
- **Installer un package :**
  ```bash
  make npm-front ARGS="install <nom-du-package>"
  ```

- **Lancer un script (lint, etc.) :**
   ```bash
   make npm-front ARGS="run <nom-du-script>"
   ```

### Les Interdits

A ne **JAMAIS** faire :
1.  **Lancer un serveur de dev à la main** (`npm run dev`, `artisan serve`). (Docker le fait déjà avec `docker compose up`).
2.  **Installer PHP, Composer, Node ou une autre dépendance sur ton PC.** (Inutile, tout est déjà dans Docker).
3.  **Modifier du code en dehors de ton éditeur** (pas de `docker exec` pour éditer des fichiers, c'est risqué et pas nécessaire).
