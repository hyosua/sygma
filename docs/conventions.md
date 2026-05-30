# Conventions — Sygma

## Ce document centralise les règles de développement du projet.

## Table des matières

1. [Terminologie](#terminologie)
2. [Git](#git)
3. [Backend PHP / Laravel](#backend-php--laravel)
4. [API REST](#api-rest)
5. [Tests](#tests)
6. [Frontend Vue.js](#frontend-vuejs)

---

## Terminologie

Ces termes sont utilisés de manière cohérente dans le code, les commits, et la documentation.

| Terme correct          | À éviter                                      |
| ---------------------- | --------------------------------------------- |
| `enseignant`           | `professeur`, `prof`                          |
| `étudiant`             | `eleve`, `apprenant`                          |
| `séance`               | `session` (réservé aux sessions d'émargement) |
| `session d'émargement` | `session de présence`, `cours`                |
| `groupe`               | `promotion`, `classe`                         |
| `présence`             | `attendance`                                  |
| `émargement`           | `pointage`                                    |

---

## Git

### Branches

Format : `type/description-courte`

| Type        | Usage                                       |
| ----------- | ------------------------------------------- |
| `feat/`     | Nouvelle fonctionnalité                     |
| `fix/`      | Correction de bug                           |
| `refactor/` | Refactoring sans changement de comportement |
| `docs/`     | Documentation uniquement                    |
| `tests/`    | Ajout ou correction de tests                |
| `style/`    | Formatage, lint (pas de changement logique) |

Exemples : `feat/gestion-seances-back`, `fix/jeton-expiration`, `docs/api-emargement`

### Commits

Format : `type/ description en français`

```
feat/ ajout de la validation de présence par QR Code
fix/ correction du calcul d'expiration du jeton
refactor/ extraction de la logique métier dans SeanceService
docs/ mise à jour de la documentation API
tests/ ajout tests EmargementService
style/ correction lint PHP
```

**Règles :**

- Description courte et concrète (ce que ça fait, pas comment)
- Un commit = une unité logique de travail
- Ne jamais committer sur `main` directement — toujours passer par une PR

### Pull Requests

- Toute PR vers `main` doit passer le CI (tests PHPUnit + build Vite)
- Si vous avez des doutes, faire relire par un autre membre avant de merger

---

## Backend PHP / Laravel

### Style de code

- Standard **PSR-12**, appliqué automatiquement par **Laravel Pint** (hook pre-commit + CI)
- Activer le hook au premier clone : `git config core.hooksPath .githooks`

### Architecture

- **La logique métier va dans les Services**, jamais dans les contrôleurs
- Les contrôleurs ne font que : valider les entrées, appeler le service, retourner la réponse
- **Les exceptions métier ne sont jamais catchées dans les contrôleurs** — elles remontent au `Handler.php`

```php
// ✅ Correct
public function supprimer(Seance $seance)
{
    $this->seanceService->supprimerSeance($seance);
    return response()->json(null, 204);
}

// ❌ Incorrect
public function supprimer(Seance $seance)
{
    try {
        $this->seanceService->supprimerSeance($seance);
        return response()->json(null, 204);
    } catch (SessionEmargementActiveException $e) {
        return response()->json(['message' => $e->getMessage()], 409);
    }
}
```

### Exceptions métier

- Chaque exception métier a son propre fichier dans `app/Exceptions/`
- Organisées par domaine : `Emargement/`, `Seance/`
- Le mapping exception → code HTTP est défini **une seule fois** dans `app/Exceptions/Handler.php`
- Quand on crée une nouvelle exception, penser à l'ajouter dans `Handler.php`

### Nommage

| Élément      | Convention | Exemple                                      |
| ------------ | ---------- | -------------------------------------------- |
| Classes      | PascalCase | `SeanceService`, `EmargementController`      |
| Méthodes     | camelCase  | `creerSeance()`, `validerPresenceParJeton()` |
| Variables    | camelCase  | `$seanceActive`, `$parPage`                  |
| Colonnes BDD | snake_case | `debut_a`, `enseignant_id`                   |
| Routes API   | kebab-case | `/sessions-emargement`, `/valider-qr`        |

---

## API REST

### Codes HTTP

| Code  | Quand l'utiliser                                                                   |
| ----- | ---------------------------------------------------------------------------------- |
| `200` | Succès d'une lecture ou d'une action sans création                                 |
| `201` | Ressource créée avec succès                                                        |
| `204` | Succès sans contenu retourné (suppression)                                         |
| `404` | Ressource introuvable                                                              |
| `409` | Conflit métier (doublon, créneau occupé, session active)                           |
| `422` | Règle métier non respectée (jeton invalide, séance inactive, étudiant non inscrit) |
| `500` | Erreur interne non prévue                                                          |

### Format des réponses d'erreur

Toujours retourner un objet JSON avec une clé `message` :

```json
{ "message": "Description de l'erreur." }
```

Ne jamais retourner une chaîne brute :

```php
// ✅ Correct
return response()->json(['message' => 'Ressource introuvable.'], 404);

// ❌ Incorrect
return response()->json('Ressource introuvable.', 404);
```

---

## Tests

- Un test = un comportement précis à vérifier
- Nommer les tests en français, de façon lisible : `test_retourne_erreur_si_etudiant_non_inscrit`
- Utiliser `RefreshDatabase` pour isoler chaque test
- Vérifier les codes HTTP **et** le contenu de la réponse quand c'est pertinent
- Les tests tournent sur la base `sygma_test` (PostgreSQL), isolée de la base de dev — utiliser `make test`
- Les tests doivent passer en CI avant tout merge

---

## Frontend React

- Composants fonctionnels uniquement (pas de classes)
- Un composant = un fichier, nommé en PascalCase (`SeanceCard.jsx`)
- Les appels API se font via `fetch` natif avec `credentials: 'include'`
- Pas de logique métier dans les composants : extraire dans des hooks ou des fonctions utilitaires
