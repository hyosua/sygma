# Guide de Contribution - Sygma

Ce document définit les règles et les standards pour assurer la qualité et la cohérence du projet.

## 🌿 Stratégie de Branches

Utiliser des préfixes pour identifier le type de travail :

- `feat/` : Nouvelle fonctionnalité (ex: `feat/generation-qr-code`)
- `fix/` : Correction de bug (ex: `fix/calcul-retard`)
- `docs/` : Documentation (ex: `docs/api-endpoints`)
- `refactor/` : Amélioration du code sans changement fonctionnel
- `test/` : Ajout ou modification de tests

**Procédure :**
1. Toujours partir de la branche `main` à jour.
```bash
git pull origin main
```
2. Créer une branche avec un nom explicite : `git checkout -b type/description-courte`.

## 🧪 Tests & Qualité

Avant chaque Pull Request, vérifiez que votre code ne casse rien :

1. **Lancer les tests** : 
   ```bash
   sygma artisan test
   ```
2. **Vérifier le style** :
   Assurez-vous qu'aucun avertissement majeur ne remonte dans vos outils de linting habituels.

## Processus de Pull Request (PR)

1. **Push** : Envoyez votre branche sur GitHub.
2. **Ouverture** : Créez la PR vers `main`.
3. **Description** : Expliquez brièvement les changements effectués.
4. **Revue (Optionnel)** : Si vous souhaitez un retour sur votre travail, demandez une revue à un collaborateur.
5. **Merge** : Une fois prêt, le merge peut être effectué.

---

## 🛠 Guide de Développement Docker

L'environnement de développement de Sygma est entièrement conteneurisé avec Docker. Comprendre comment interagir avec ces conteneurs est essentiel pour une contribution efficace.

**La règle d'or :** Votre machine locale (PC) est votre **éditeur de code** et votre **interface de contrôle**. Le conteneur Docker est votre **environnement d'exécution**. Le code que vous écrivez sur votre machine est automatiquement synchronisé dans les conteneurs.

---

### 🐳 Guide Docker pour le Développeur Backend (PHP/Laravel)

Ce guide s'adresse au développeur backend.

#### ✅ Ce que vous faites (Vos interactions avec Docker)

Votre interaction principale avec Docker consistera à exécuter des commandes spécifiques à Laravel/PHP à l'intérieur du conteneur `backend`.

**1. Installer une nouvelle librairie Composer :**
Pour ajouter un package Composer, utilisez :
**Avec le script (recommandé) :**
```bash
sygma composer require <package>
```
**Sans le script (commande complète) :**
```bash
docker compose exec backend composer require <package>
```

**2. Exécuter une commande Artisan :**
Toutes les commandes `php artisan` doivent être exécutées dans le conteneur `backend`.
**Avec le script (recommandé) :**
```bash
sygma artisan <commande> (ex: migrate, make:model, test)
```
**Sans le script (commande complète) :**
```bash
docker compose exec backend php artisan <commande>
```

**3. Lancer les tests PHPUnit :**
**Avec le script (recommandé) :**
```bash
sygma artisan test
```
**Sans le script (commande complète) :**
```bash
docker compose exec backend php artisan test
```

#### ❌ Ce que vous ne faites JAMAIS

**1. Modifier le code à l'intérieur du conteneur :**
Écrivez et modifiez votre code PHP/Laravel sur votre machine locale avec votre IDE (VS Code, PhpStorm). Les fichiers sont automatiquement synchronisés. N'utilisez **jamais** `docker compose exec backend bash` pour tenter de modifier des fichiers avec `vim` ou `nano`.

**2. Installer PHP ou Composer en local :**
L'environnement PHP complet (PHP, Composer, extensions) est géré par le conteneur. Tenter d'installer ou d'exécuter PHP/Composer localement pourrait entraîner des erreurs de version ou de dépendances.

**3. Exécuter `php artisan serve` :**
Le serveur PHP est déjà démarré par Docker (généralement via PHP-FPM). Lancer `php artisan serve` manuellement créera un conflit de ports et est inutile.

---

### 🐳 Guide Docker pour le Développeur Frontend (React)

Ce guide s'adresse au développeur frontend.

#### ✅ Ce que vous faites (Vos interactions avec Docker)

Votre interaction principale avec Docker consistera à exécuter des commandes spécifiques à Node/NPM à l'intérieur du conteneur `frontend`.

**1. Installer une nouvelle librairie NPM :**
Pour ajouter un package (ex: `axios`), vous devez le demander au conteneur `frontend` pour que tout le monde soit synchronisé.
**Avec le script (recommandé) :**
```bash
sygma npm install axios
```
**Sans le script (commande complète) :**
```bash
docker compose exec frontend npm install axios
```
Cela mettra à jour les fichiers `package.json` et `package-lock.json` de votre projet.

**2. Lancer un script ponctuel (ex: linter) :**
Pour lancer un script défini dans votre `package.json`, la logique est la même.
**Avec le script :**
```bash
sygma npm run lint
```
**Sans le script (commande complète) :**
```bash
docker compose exec frontend npm run lint
```

#### ❌ Ce que vous ne faites JAMAIS

**1. Modifier le code à l'intérieur du conteneur :**
Vous écrivez et modifiez votre code React/JS/CSS comme d'habitude sur votre machine, avec VS Code. Grâce aux "volumes" Docker, vos fichiers sont automatiquement et instantanément synchronisés dans le conteneur. N'utilisez **jamais** `docker compose exec frontend bash` pour ensuite essayer de modifier un fichier avec `vim` ou `nano`.

**2. Lancer le serveur de développement manuellement :**
Le serveur de développement (`vite` ou `npm run dev`) est **automatiquement lancé pour vous** par Docker lorsque vous faites `sygma start` ou `docker compose up -d`. Tenter de le lancer manuellement dans le conteneur créera des conflits de ports et est inutile.

---