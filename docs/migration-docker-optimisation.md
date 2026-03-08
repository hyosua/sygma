# Migration Docker — Optimisation des images et des permissions

Ce guide s'applique si vous avez déjà une installation Sygma fonctionnelle et que vous venez de puller ce PR.

---

## Tester la PR sans l'appliquer

> Si vous avez des modifications en cours, sauvegardez-les d'abord avec un commit.

Basculez temporairement sur la branche de la PR :

```bash
git fetch origin
git checkout fix/docker-permissions-makefile
```

Suivez ensuite la procédure de mise à jour ci-dessous. Les dépendances n'ont pas à être retéléchargées : elles vivent dans les conteneurs Docker, pas dans votre dossier local.

Une fois votre test terminé, revenez sur votre branche :

```bash
git checkout -
```

---

## Ce qui a changé

- L'image backend passe de `php:8.3-fpm` (Debian) à `php:8.3-cli-alpine` : build plus rapide, image plus légère.
- Node.js n'est plus installé via `curl | bash` (lent) mais via `apk` (natif Alpine).
- Les layers Docker sont réordonnés pour mieux exploiter le cache lors des builds suivants.
- Les fichiers créés dans le conteneur (logs, cache, `artisan make:*`) appartiennent désormais à votre utilisateur et non plus à root.
- `.dockerignore` ajoutés sur backend et frontend : `vendor/`, `node_modules/`, `.git/` ne sont plus copiés lors du build.

---

## Prérequis

- **Docker Desktop** doit être lancé avant de commencer.
- Toutes les commandes ci-dessous doivent être exécutées dans un **terminal WSL** (Ubuntu), pas dans PowerShell ni CMD.

Pour ouvrir un terminal WSL depuis VS Code : `Ctrl + ù` (le terminal s'ouvre déjà en WSL si vous êtes connecté via l'extension WSL).

- **`make`** doit être installé :

```bash
   which make || sudo apt install make
```

> Si vous ne souhaitez pas installer `make`, des équivalents `docker compose` sont indiqués à chaque étape concernée.

---

## Procédure de mise à jour

### 1. Récupérer les changements

```bash
git pull
```

### 2. Arrêter et supprimer les anciens conteneurs

```bash
docker compose down
```

> Vos données PostgreSQL sont conservées (`--volumes` n'est pas utilisé).

### 3. Supprimer les anciennes images

Les anciennes images ne sont pas compatibles avec les nouveaux Dockerfiles. Elles doivent être supprimées pour forcer la reconstruction.

```bash
docker compose down --rmi local
```

Cette commande supprime les images construites localement par Compose. Si vous préférez cibler les images manuellement :

```bash
docker rmi sygma-backend sygma-frontend
```

Si l'une des deux n'existe pas encore chez vous, ignorez l'erreur.

### 4. Reconstruire les images

```bash
docker compose build
```

Le premier build prend quelques minutes (téléchargement de la nouvelle image de base). Les builds suivants seront significativement plus rapides grâce au cache Docker.

### 5. Relancer les services

```bash
make start
```

> Sans `make` :
> ```bash
> SYGMA_UID=$(id -u) SYGMA_GID=$(id -g) docker compose up -d
> ```

### 6. Vérifier que tout fonctionne

```bash
docker compose ps
```

Tous les conteneurs doivent être en statut `Up`.

```bash
make artisan ARGS="migrate:status"
```

> Sans `make` :
> ```bash
> docker compose exec -u $(id -u):$(id -g) backend php artisan migrate:status
> ```

Toutes les migrations doivent apparaître comme appliquées.

Tout est prêt. Vérifiez que les trois services répondent :

| Service | URL |
|---------|-----|
| Front-end | http://localhost:3000 |
| Back-end (API) | http://localhost:8000 |
| Adminer (BDD) | http://localhost:8080 |

Pour Adminer, connectez-vous avec : système `PostgreSQL`, serveur `db`, utilisateur `sygma`, mot de passe `sygma_pass`, base `sygma`.

---

## En cas de problème

### "permission denied" au démarrage du backend

Récupérez la propriété des fichiers depuis le terminal WSL :

```bash
sudo chown -R $USER:$USER ./backend ./frontend
```

Puis relancez :

```bash
make start
```

### L'image ne se reconstruit pas (build identique à avant)

Vérifiez que vous avez bien supprimé les anciennes images à l'étape 3. Si elles sont toujours listées dans `docker images`, forcez leur suppression :

```bash
docker rmi -f sygma-backend sygma-frontend
```

### Les conteneurs démarrent mais le frontend affiche une erreur réseau

Attendez quelques secondes que le backend soit complètement démarré, puis rafraîchissez la page. Le backend peut mettre quelques instants à être prêt après le démarrage des conteneurs.

---

### Le frontend est inaccessible (http://localhost:3000 ne répond pas)

Vérifiez que le conteneur frontend est bien en cours d'exécution :

```bash
docker compose ps
```

Si le statut est `Exited` ou `Restarting`, consultez les logs :

```bash
docker compose logs frontend
```

Si vous voyez une erreur `EACCES` ou `permission denied` dans les logs :

```bash
sudo chown -R $USER:$USER ./frontend
docker compose run --rm -u "$(id -u):$(id -g)" frontend npm install
make start
```

---

### Le backend est inaccessible (http://localhost:8000 ne répond pas ou erreur 500)

Vérifiez que le conteneur backend est bien en cours d'exécution :

```bash
docker compose ps
```

Si le statut est `Exited` ou `Restarting`, consultez les logs :

```bash
docker compose logs backend
```

**Cas 1 — Erreur `EACCES` ou `permission denied` sur `vendor/` ou `storage/` :**

```bash
sudo chown -R $USER:$USER ./backend
make repair
```

**Cas 2 — Erreur Laravel `No application encryption key` :**

La clé d'application n'a pas été générée. Exécutez :

```bash
docker compose run --rm -u "$(id -u):$(id -g)" backend php artisan key:generate
make start
```

**Cas 3 — Erreur de connexion à la base de données :**

Vérifiez que le conteneur `db` est bien `Up` et healthy :

```bash
docker compose ps db
```

Si `db` est en erreur, relancez-le :

```bash
docker compose restart db
# Attendez quelques secondes, puis :
make start
```
