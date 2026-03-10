# CI/CD — Intégration continue sur Sygma

Ce guide explique comment fonctionne le CI (vérification automatique du code), comment lire les résultats, et comment corriger les erreurs avant de committer.

---

## Table des matières

1. [C'est quoi le CI ?](#cest-quoi-le-ci-)
2. [Quand est-ce que ca se déclenche ?](#quand-est-ce-que-ca-se-déclenche-)
3. [Ce que vérifie le CI](#ce-que-vérifie-le-ci)
4. [Lire les résultats](#lire-les-résultats)
5. [Corriger les erreurs](#corriger-les-erreurs)
6. [Hook pre-commit — correction automatique locale](#hook-pre-commit--correction-automatique-locale)
7. [Référence rapide](#référence-rapide)

---

## C'est quoi le CI ?

Le CI (Continuous Integration) est un robot qui vérifie automatiquement ton code à chaque push ou pull request. Il tourne sur GitHub Actions — tu n'as rien à installer, ca s'exécute dans le cloud.

Son rôle : détecter les problèmes (mauvais formatage, code cassé, build raté) avant qu'ils arrivent sur `main`.

---

## Quand est-ce que ca se déclenche ?

Le CI se lance automatiquement dans deux cas :

- **Quand tu ouvres ou mets à jour une Pull Request** vers `main`
- **Quand tu pushs directement sur `main`**

Il ne se lance que si les fichiers concernés ont changé :
- Modification dans `backend/` → CI Backend
- Modification dans `frontend/` → CI Frontend

---

## Ce que vérifie le CI

### Backend (`backend/`)

| Vérification | Outil | Bloquant ? | Description |
|---|---|---|---|
| Lint Pint | Laravel Pint | Non | Formatage du code PHP (PSR-12) |
| Lint PHPCS | PHP_CodeSniffer | Non | Règles de style supplémentaires |
| Tests | PHPUnit | **Oui** | Tests unitaires et fonctionnels |

### Frontend (`frontend/`)

| Vérification | Outil | Bloquant ? | Description |
|---|---|---|---|
| Lint ESLint | ESLint | Non | Qualité et style du code JS/TS |
| Build | Vite | **Oui** | Compilation du projet React |

**Bloquant = Oui** signifie que le CI échoue et empêche le merge si cette étape est en erreur.

**Bloquant = Non** signifie que c'est un avertissement : le CI reste vert, mais le problème est signalé.

---

## Lire les résultats

### Trouver le rapport

1. Va sur ta Pull Request GitHub
2. Fais défiler jusqu'en bas, section **"Checks"**
3. Clique sur **"Details"** à côté du job concerné
4. Clique sur l'onglet **"Summary"** (en haut à gauche du job)

Tu verras un tableau comme celui-ci :

```
| Vérification  | Statut     | Bloquant |
|---------------|------------|----------|
| Lint Pint     | OK         | Non      |
| Lint PHPCS    | WARNING    | Non      |
| Tests PHPUnit | OK         | Oui      |
```

### Comprendre les statuts

- **OK** — tout est bon
- **WARNING** — problème de style détecté, non bloquant. Corriger avec `make lint-fix`
- **FAIL** — erreur critique, le merge est bloqué
- **SKIP** — l'étape n'a pas pu s'exécuter (généralement parce qu'une étape précédente a échoué)

### Voir le détail d'une erreur

Dans le Summary, les erreurs sont affichées dans des blocs dépliables. Clique sur le triangle pour voir :
- Lint : les fichiers concernés et les lignes à corriger
- Tests : le fichier et la ligne exacte du test en échec (`MonTest.php:42`)
- Build : la sortie complète de Vite avec le message d'erreur

---

## Corriger les erreurs

### Lint (WARNING) — Problème de formatage

Le lint détecte des problèmes de style (indentation, espaces, ordre des imports...). Ce n'est pas bloquant mais c'est mieux de corriger.

**Correction automatique en une commande :**

```bash
make lint-fix
```

Cela corrige PHP et JS/TS en une seule fois.

**Ou manuellement :**

```bash
# Backend uniquement
cd backend
./vendor/bin/pint
./vendor/bin/phpcbf --standard=phpcs.xml

# Frontend uniquement
cd frontend
npm run lint:fix
```

Ensuite re-commit et push :

```bash
git add .
git commit -m "style: correction lint"
git push
```

### Tests (FAIL) — Un test a échoué

Le Summary affiche le fichier et la ligne du test en échec, par exemple :

```
FAIL tests/Feature/EmargementTest.php:87
Failed asserting that false is true.
```

1. Ouvre le fichier indiqué à la ligne indiquée
2. Lis le message d'erreur pour comprendre ce qui est attendu vs ce qui est retourné
3. Corrige le code (ou le test s'il est incorrect)
4. Vérifie localement avant de push :

```bash
make artisan test
# ou directement :
docker compose exec backend ./vendor/bin/phpunit --testdox
```

### Build (FAIL) — Le build Vite a échoué

Le build échoue généralement à cause d'une erreur TypeScript ou d'un import manquant.

Le Summary affiche la sortie complète de Vite. Recherche les lignes contenant `error` ou `Error`, par exemple :

```
src/components/MonComposant.tsx:12:5: error TS2304: Cannot find name 'maVariable'.
```

1. Ouvre le fichier à la ligne indiquée
2. Corrige l'erreur
3. Vérifie localement :

```bash
make npm-front run build
# ou directement :
docker compose exec frontend npm run build
```

---

## Hook pre-commit — correction automatique locale

Pour éviter d'avoir des warnings lint dans le CI, un hook git corrige automatiquement le style **avant chaque commit**.

### Activer le hook (une seule fois par clone)

```bash
git config core.hooksPath .githooks
```

Si tu utilises `make install` pour un nouveau setup, c'est fait automatiquement.

### Ce que fait le hook

Quand tu fais `git commit`, le hook :

1. Détecte les fichiers `.php` que tu as stagés → les reformatte avec Pint et phpcbf
2. Détecte les fichiers `.js/.ts/.tsx` que tu as stagés → les reformatte avec ESLint
3. Re-stage automatiquement les fichiers modifiés

Le commit continue normalement avec le code corrigé. Tu n'as rien à faire.

### Désactiver le hook temporairement

```bash
git commit --no-verify -m "mon message"
```

A utiliser avec parcimonie — le CI reste le filet de sécurité.

---

## Référence rapide

| Situation | Commande |
|---|---|
| Corriger tout le lint (PHP + JS) | `make lint-fix` |
| Vérifier le lint sans corriger | `make lint-check` |
| Lancer les tests backend | `make artisan test` |
| Builder le frontend | `make npm-front run build` |
| Activer le hook pre-commit | `git config core.hooksPath .githooks` |
