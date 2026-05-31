# Clôture et Passation du Projet Sygma

Ce document détaille les étapes nécessaires pour finaliser le projet en fin d'année et assurer une reprise fluide par une nouvelle équipe d'étudiants.

## 1. Migration vers une Organisation GitHub

Actuellement hébergé sur un compte personnel, le projet doit être transféré vers une organisation pour une gestion collective et pérenne.

### Procédure de transfert

1. **Création de l'Organisation :**
   - Se connecter à GitHub -> `Settings` -> `Organizations` -> `New organization`.
   - Choisir le plan **Free**.
   - Inviter tous les membres actuels en tant qu'**Owners**.

2. **Transfert du Repository :**
   - Aller dans les `Settings` du repo `sygma`.
   - Section `Danger Zone` -> `Transfer ownership`.
   - Saisir le nom de la nouvelle organisation.
   - Confirmer le transfert (les redirections d'URL sont gérées par GitHub).

3. **Mise à jour des accès locaux :**
   Chaque développeur devra mettre à jour son URL de remote :
   ```bash
   git remote set-url origin https://github.com/sygma/sygma.git
   ```

## 2. Nettoyage et Archivage du Code

### Gestion des branches

- Fusionner toutes les Pull Requests ouvertes.
- Supprimer les branches de fonctionnalités (`feat/*`, `fix/*`) obsolètes.
- Créer un **Tag de version** pour marquer l'état final du projet étudiant :
  ```bash
  git tag -a v1.0.0-final -m "Version finale du projet sygma 2026"
  git push origin v1.0.0-final
  ```

### Secrets et Environnement

- S'assurer que les fichiers `.env.example` (backend et frontend) sont parfaitement à jour.
- **Ne jamais inclure de vrais secrets** dans le dépôt.
- Documenter la procédure pour obtenir les clés API réelles (ex: ngrok, mailtrap) si elles ne sont pas fournies dans la passation physique.

## 3. Documentation Technique

Avant le départ de l'équipe actuelle, vérifier que :

- Le `README.md` principal est à jour avec la procédure d'installation.
- L'`api_doc.md` reflète l'état actuel des endpoints.
- Le fichier `doc_technique.md` explique bien les choix d'architecture (choix de Laravel/React, structure de la BDD).
- Les tests (`make test`) passent tous à 100%.

## 4. Données et Base de données

- Fournir un export SQL de la structure (si des changements majeurs hors migrations existent).
- Vérifier que le `DatabaseSeeder` génère bien un jeu de données de test complet et fonctionnel pour la nouvelle équipe.

## 5. Transfert Railway & Vercel

L'infrastructure de production tourne sur Railway (backend) et Vercel (frontend). Un `git push` sur `main` déclenche les deux déploiements automatiquement.

### Railway (backend)

1. Inviter le responsable de la nouvelle équipe comme **Admin** dans le projet Railway existant, ou transférer le projet via `Settings` -> `Transfer Project`.
2. S'assurer que les variables d'environnement suivantes sont bien renseignées dans le tableau de bord Railway :

| Variable | Valeur |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | générer via `php artisan key:generate --show` |
| `APP_URL` | URL Railway du service |
| `DB_*` | fournis automatiquement par le plugin PostgreSQL Railway |
| `SESSION_DRIVER` | `database` |
| `SESSION_SAME_SITE` | `none` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SANCTUM_STATEFUL_DOMAINS` | domaine Vercel sans `https://` |
| `FRONTEND_URL` | URL Vercel complète |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | credentials Google OAuth |
| `GOOGLE_REDIRECT_URI` | `https://<domaine-railway>/auth/google/callback` |
| `MAIL_MAILER` | `smtp` (ou `log` pour désactiver) |

> **Attention :** ne jamais activer `SEED_ON_DEPLOY=true` en production - cela efface toute la base de données.

### Vercel (frontend)

1. Inviter le responsable de la nouvelle équipe comme **Owner** dans le projet Vercel, ou le transférer via `Settings` -> `Transfer Project`.
2. Vérifier que la variable d'environnement est bien renseignée :

| Variable | Valeur |
|---|---|
| `VITE_API_URL` | URL Railway + `/api` (ex: `https://backend-xxx.up.railway.app/api`) |

3. S'assurer que le fichier `frontend/vercel.json` est présent dans le repo (il gère le routing SPA).

### Vérification post-passation

Après que la nouvelle équipe a pris la main, faire un push de test sur `main` et vérifier :
- Railway -> onglet "Deployments" : build vert, migrations passées dans les logs.
- Vercel -> onglet "Deployments" : build Vite vert.
- Tester une connexion complète depuis le frontend en production.

## 6. Maintenance et CI/CD

- Vérifier que les GitHub Actions (`ci-backend.yml`, `ci-frontend.yml`) sont au vert.
- Si des services tiers sont utilisés (hébergement, registries), transférer la propriété des comptes ou documenter les accès.

## 7. Contacts

Hyosua Colléter - [colleterhyosua@gmail.com](mailto:colleterhyosua@gmail.com)
Yahaya Coulibaly -
Emmanuelle Nsossani -
