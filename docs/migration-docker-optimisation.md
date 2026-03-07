# Migration Docker — Optimisation des images et des permissions

Ce guide s'applique si vous avez déjà une installation Sygma fonctionnelle et que vous venez de puller ce PR.

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
docker rmi sygma-backend:latest sygma-frontend:latest
```

Si l'une des deux n'existe pas encore chez vous, ignorez l'erreur.

### 4. Reconstruire les images

```bash
docker compose build
```

Le premier build prend quelques minutes (téléchargement de la nouvelle image de base). Les builds suivants seront significativement plus rapides grâce au cache Docker.

### 5. Relancer les services

```bash
./sygma.sh start
```

### 6. Vérifier que tout fonctionne

```bash
docker compose ps
```

Tous les conteneurs doivent être en statut `Up`.

```bash
./sygma.sh artisan migrate --status
```

Toutes les migrations doivent apparaître comme appliquées. Ouvrir http://localhost:3000 dans le navigateur.

---

## En cas de problème

### "permission denied" au démarrage du backend

Récupérez la propriété des fichiers depuis le terminal WSL :

```bash
sudo chown -R $USER:$USER ./backend ./frontend
```

Puis relancez :

```bash
./sygma.sh start
```

### L'image ne se reconstruit pas (build identique à avant)

Vérifiez que vous avez bien supprimé les anciennes images à l'étape 3. Si elles sont toujours listées dans `docker images`, forcez leur suppression :

```bash
docker rmi -f sygma-backend:latest sygma-frontend:latest
```

### Les conteneurs démarrent mais le frontend affiche une erreur réseau

Attendez quelques secondes que le backend soit complètement démarré, puis rafraîchissez la page. Le backend dispose maintenant d'un healthcheck — le frontend attend qu'il soit prêt avant de se connecter.
