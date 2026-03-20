# Laravel Telescope — Guide Sygma

Telescope est un outil de débogage visuel accessible sur `http://localhost:8000/telescope` en environnement local.

---

## Accès

```
http://localhost:8000/telescope
```

> Nécessite `APP_ENV=local` dans `.env`. Telescope n'est pas actif en test ou en production.

---

## Watchers utiles sur ce projet

### Requests — déboguer une route API

Le watcher le plus utilisé. Chaque appel API apparaît avec :
- la méthode, l'URL, le code de réponse
- les headers (dont le token Sanctum)
- le body de la requête et de la réponse

**Exemple — déboguer `POST /api/sessions-emargement` :**

Tu envoies une requête depuis le front et obtiens une 422. Dans Telescope > Requests, tu cliques sur la requête et tu vois directement :
```json
// Request body
{ "seance_id": 12, "is_methode_qr": true }

// Response body
{ "message": "La séance associée n'est pas active." }
```
Immédiatement tu sais que la séance 12 n'est pas en cours.

---

### Queries — identifier les requêtes SQL

Affiche chaque requête SQL avec ses bindings et son temps d'exécution. Les requêtes > 100ms sont marquées comme **slow**.

**Exemple — vérifier l'optimisation `withCount` sur les séances :**

Après `GET /api/seances`, tu peux confirmer que le nombre d'inscrits est chargé en une seule requête plutôt que N+1 :
```sql
-- Ce que tu VEUX voir (1 requête)
SELECT *, COUNT(inscriptions.id) as nombre_inscrits
FROM seances LEFT JOIN inscriptions ON ...

-- Ce que tu NE VEUX PAS voir (N+1)
SELECT COUNT(*) FROM inscriptions WHERE cours_id = 1
SELECT COUNT(*) FROM inscriptions WHERE cours_id = 2
SELECT COUNT(*) FROM inscriptions WHERE cours_id = 3
...
```

**Exemple — déboguer `validerPresenceParJeton` :**

Le service fait plusieurs vérifications en base (session, étudiant, doublon). Tu peux voir exactement combien de requêtes sont générées et lesquelles sont lentes.

---

### Exceptions — voir les erreurs métier

Affiche la stack trace complète de toutes les exceptions, y compris celles que tu catches et retournes en JSON.

**Exemple — `JetonExpireException` silencieuse :**

Si un étudiant tente de scanner un QR expiré, l'API retourne un 422 propre. Mais dans Telescope > Exceptions, tu vois la stack trace complète :
```
App\Exceptions\Emargement\JetonExpireException
  Le jeton QR a expiré.

  #0 app/Services/EmargementService.php:65
  #1 app/Http/Controllers/EmargementController.php:58
  ...
```
Utile pour savoir exactement quelle ligne a levé l'exception.

---

### Gate — vérifier les rôles Spatie

Affiche chaque vérification de rôle/permission avec son résultat (`allowed` / `denied`).

**Exemple — un étudiant tente d'accéder à une route enseignant :**

```
Ability : role:enseignant|gestionnaire
Result  : denied
User    : etudiant@sygma.com (id: 42)
```

Utile pour déboguer un 403 inattendu ou vérifier qu'un middleware de rôle est bien appliqué.

---

### Models — suivre les mutations Eloquent

Affiche chaque `created`, `updated`, `deleted` sur les modèles Eloquent.

**Exemple — suivre la création d'une `Presence` :**

Quand un étudiant émarger, tu vois apparaître :
```
Model  : App\Models\Presence (created)
Attrs  : session_emargement_id=5, etudiant_id=23, statut=present, scanne_a=2026-03-20 14:32:01
```

Utile pour confirmer qu'une présence est bien enregistrée et avec les bons attributs, sans avoir à aller en base.

---

## Cas d'usage typiques

| Situation | Watcher à ouvrir |
|---|---|
| Route retourne une erreur inattendue | Requests + Exceptions |
| Réponse lente | Queries (chercher les slow queries) |
| 403 alors que l'utilisateur a le bon rôle | Gate |
| Présence non enregistrée en base | Models |
| Doute sur le nombre de requêtes SQL générées | Queries |

---

## Désactiver temporairement

Dans `.env` :
```env
TELESCOPE_ENABLED=false
```

Utile si Telescope ralentit les tests manuels sur des jeux de données volumineux.
