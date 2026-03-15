# Documentation Technique — Sygma

## Table des matières

1. [Présentation du projet](#présentation-du-projet)
2. [Stack technique](#stack-technique)
3. [Architecture générale](#architecture-générale)
4. [Modèle de données](#modèle-de-données)
5. [Architecture backend](#architecture-backend)
6. [Gestion des erreurs HTTP](#gestion-des-erreurs-http)
7. [Authentification et autorisations](#authentification-et-autorisations)
8. [Tests](#tests)
9. [CI/CD](#cicd)
10. [Sécurité](#sécurité)

---

## Présentation du projet

Sygma est une application web de gestion de présence numérique destinée aux établissements d'enseignement supérieur. Elle permet aux enseignants de démarrer des sessions d'émargement par QR Code ou manuellement, aux étudiants de valider leur présence, et aux gestionnaires de consulter et valider les feuilles de présence.

**Rôles utilisateurs :**

| Rôle | Responsabilités |
|---|---|
| Étudiant | Scanner le QR Code ou être marqué présent manuellement |
| Enseignant | Créer/gérer des séances, démarrer et clôturer l'émargement |
| Gestionnaire | Consulter, valider et exporter les feuilles de présence |

---

## Stack technique

| Composant | Technologie |
|---|---|
| Backend | Laravel 11 (PHP 8.3), API REST |
| Frontend | Vue.js 3 / Vite |
| Base de données (prod) | MySQL (via Docker) |
| Base de données (tests) | PostgreSQL (`sygma_test`, isolée de la base de dev) |
| Authentification | Laravel Sanctum |
| Gestion des rôles | Spatie Laravel Permission |
| Conteneurisation | Docker / Docker Compose |
| CI/CD | GitHub Actions |

---

## Architecture générale

L'application suit une architecture **client-serveur** découplée :

- Le **frontend** (Vue.js) consomme l'API REST via des appels HTTP
- Le **backend** (Laravel) expose une API JSON préfixée `/api`
- Les deux communiquent exclusivement en JSON

```
Navigateur / Mobile
       │
       │ HTTP/JSON
       ▼
  API Laravel (/api)
       │
  ┌────┴────┐
  │  Routes │
  └────┬────┘
       │
  ┌────┴────────┐
  │ Contrôleurs │  — validation des entrées, orchestration
  └────┬────────┘
       │
  ┌────┴────┐
  │ Services │  — logique métier
  └────┬────┘
       │
  ┌────┴────┐
  │ Modèles │  — accès base de données (Eloquent ORM)
  └─────────┘
```

---

## Modèle de données

Le schéma complet (MCD/MLD) est disponible dans `docs/specs/MCD/`.

**Tables principales :**

| Table | Description |
|---|---|
| `users` | Étudiants et enseignants, avec rôles Spatie |
| `groupes` | Groupes d'étudiants |
| `cours` | Matières enseignées |
| `seances` | Séances planifiées (`cours_id`, `enseignant_id`, `groupe_id`, `debut_a`, `fin_a`, `salle`) |
| `inscriptions` | Inscription étudiant ↔ cours |
| `sessions_emargement` | Session d'émargement liée à une séance (`jeton`, `expire_a`, `is_methode_qr`, lat/lon) |
| `presences` | Présence enregistrée (`session_id`, `etudiant_id`, `statut`, `scanne_a`, lat/lon) |

**Relations clés :**

- Une `Seance` appartient à un `Cours`, un `User` (enseignant) et un `Groupe`
- Une `SessionEmargement` appartient à une `Seance`
- Une `Presence` appartient à une `SessionEmargement` et à un `User` (étudiant)

---

## Architecture backend

### Couche Service

La logique métier est isolée dans des classes de service, séparée des contrôleurs :

**`EmargementService`** — gestion du cycle de vie d'une session d'émargement :

| Méthode | Rôle |
|---|---|
| `demarrerSession(Seance, bool, array)` | Crée une `SessionEmargement`, génère le jeton |
| `validerPresenceParJeton(jeton, User, array)` | Valide un scan QR (jeton, expiration, séance active, inscription, doublon) |
| `enregistrerPresence(SessionEmargement, User)` | Enregistre une présence manuelle |
| `rafraichirJeton(SessionEmargement)` | Rotation du jeton (nouveau jeton + 20s d'expiration) |
| `cloturerSession(SessionEmargement)` | Met `expire_a = now()` |

**`SeanceService`** — gestion des séances :

| Méthode | Rôle |
|---|---|
| `getSeances(array)` | Liste paginée avec filtres (statut, enseignant, groupe, dates) |
| `getSeance(Seance)` | Détail d'une séance avec relations et nombre d'inscrits |
| `creerSeance(array)` | Crée une séance après vérification des conflits |
| `modifierSeance(Seance, array)` | Modifie une séance après vérification des conflits |
| `supprimerSeance(Seance)` | Supprime une séance |

**Règles métier notables :**
- Durée de validité d'un jeton QR : **20 secondes** (constante `DUREE_VALIDITE_JETON`)
- Vérification de conflit de créneau pour l'enseignant et la salle à la création/modification d'une séance
- Impossible de modifier ou supprimer une séance si une session d'émargement est active

### Exceptions métier

Les exceptions métier sont organisées par domaine dans `app/Exceptions/` :

```
app/Exceptions/
├── Emargement/
│   ├── DejaEmargeException.php
│   ├── EtudiantNonInscritException.php
│   ├── JetonExpireException.php
│   └── JetonInvalideException.php
└── Seance/
    ├── ConflitSeanceException.php
    ├── SalleOccupeeException.php
    ├── SeanceNonActiveException.php
    ├── SeancePasseeException.php
    └── SessionEmargementActiveException.php
```

---

## Gestion des erreurs HTTP

### Principe

Les exceptions métier ne sont **jamais catchées dans les contrôleurs**. Elles remontent automatiquement jusqu'au gestionnaire global `app/Exceptions/Handler.php`, qui les traduit en réponses JSON cohérentes.

```
Contrôleur → Service → Exception levée
                  ↓ (non catchée)
         app/Exceptions/Handler.php
                  ↓
         Réponse JSON avec le bon code HTTP
```

Ce choix évite la duplication de code et garantit une réponse uniforme sur toutes les routes.

### Tableau des codes HTTP

| Code | Signification | Exemples |
|---|---|---|
| `200` | Succès (lecture, action) | GET séances, clôturer session |
| `201` | Ressource créée | Créer séance, créer utilisateur |
| `204` | Succès sans contenu | Supprimer séance |
| `404` | Ressource introuvable | ID inexistant en base |
| `409 Conflict` | Conflit métier | Créneau déjà occupé, salle déjà prise, doublon de présence |
| `422 Unprocessable` | Règle métier non respectée | Jeton invalide/expiré, séance non active, étudiant non inscrit |
| `500` | Erreur interne | Erreur non prévue (voir section Sécurité) |

### Format de réponse d'erreur

Toutes les erreurs retournent un JSON de la forme :

```json
{
  "message": "Description de l'erreur."
}
```

---

## Authentification et autorisations

L'authentification est assurée par **Laravel Sanctum** (tokens de session).

Les rôles (`étudiant`, `enseignant`) sont gérés par **Spatie Laravel Permission**.

> **Note** : L'authentification Sanctum est temporairement désactivée sur les routes d'émargement et de séances pour faciliter les tests. Elle sera réactivée avant la mise en production.

---

## Tests

Les tests sont situés dans `backend/tests/` et utilisent **PHPUnit** avec `RefreshDatabase`.

Ils tournent sur une base PostgreSQL dédiée (`sygma_test`), isolée de la base de dev (`sygma`). La base est créée automatiquement au premier `make test`. La variable `DB_DATABASE` est forcée dans `tests/bootstrap.php` pour contourner les variables d'environnement docker-compose.

| Fichier | Couverture |
|---|---|
| `EmargementServiceTest.php` | Tests unitaires du service (démarrage session, scan QR valide/invalide/expiré, doublon, séance inactive, étudiant non inscrit) |
| `SeanceControllerTest.php` | Tests des routes séances (liste, détail, suppression, 404) |
| `SessionEmargementTest.php` | Tests des routes d'émargement (démarrage, clôture, statut, présence manuelle) |
| `GroupManagementTest.php` | Tests de gestion des groupes |

**Lancer les tests :**

```bash
make test
```

---

## CI/CD

Le pipeline CI/CD est décrit en détail dans `docs/ci-cd.md`.

**Résumé :**
- Déclenché sur chaque push et Pull Request vers `main`
- Backend : lint PHP (Pint + PHPCS, non bloquant) + tests PHPUnit (**bloquant**)
- Frontend : lint ESLint (non bloquant) + build Vite (**bloquant**)
- Hook pre-commit local : reformatage automatique avant chaque commit

---

## Sécurité

### Gestion des erreurs (OWASP)

En production, un catch-all est prévu dans `app/Exceptions/Handler.php` pour intercepter toute exception non gérée et retourner un message générique au client, sans exposer la stack trace ni les détails internes. Les erreurs sont loguées en interne via `Log::error()`.

Ce mécanisme doit être activé avant la mise en production (décommenter le bloc dans `Handler.php`) conjointement avec `APP_DEBUG=false` dans `.env`.

### Anti-fraude QR Code

- Le jeton QR expire toutes les **20 secondes** et est renouvelé à chaque cycle
- Un étudiant ne peut émarger qu'une seule fois par session (vérification de doublon)
- La géolocalisation (comparaison position étudiant / position salle) est prévue mais non encore implémentée
