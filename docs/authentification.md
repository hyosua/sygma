# Authentification — Laravel Sanctum

## Vue d'ensemble

Sygma utilise **Laravel Sanctum** en mode **SPA** (cookie de session) pour le frontend React. Les tokens Bearer restent disponibles pour d'éventuels clients tiers ou tests via Postman.

Les deux mécanismes coexistent : Sanctum vérifie d'abord le cookie de session, puis le header `Authorization` si aucun cookie n'est présent.

---

## Ce qui est déjà en place

### `User` model — `HasApiTokens`

```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;
}
```

### `bootstrap/app.php` — middleware SPA activé

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();         // active l'auth cookie pour le SPA
    $middleware->validateCsrfTokens(except: ['api/*']); // CSRF désactivé sur /api
})
```

`statefulApi()` active automatiquement les middlewares nécessaires à l'auth par cookie (encrypt cookies, session, CSRF) pour les domaines déclarés comme stateful.

### `config/sanctum.php` — domaines stateful

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort(),
))),
```

Le frontend React tourne sur `localhost:3000` — il est bien inclus. Pour ajouter un domaine de prod, définir `SANCTUM_STATEFUL_DOMAINS` dans `.env`.

---

## Flux de connexion (SPA React)

Le frontend utilise le **`fetch` natif** (pas de dépendance Axios).

### Utilitaire : lire le cookie CSRF

```js
function getCsrfToken() {
  return document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
}
```

Laravel URL-encode le cookie, d'où le `decodeURIComponent` lors de l'envoi.

### 1. Initialiser la protection CSRF

Avant toute requête de login, le frontend doit appeler :

```js
await fetch('/sanctum/csrf-cookie', { credentials: 'include' })
```

Laravel pose un cookie `XSRF-TOKEN`. Il faut ensuite le relire et l'envoyer manuellement dans le header `X-XSRF-TOKEN` sur les requêtes suivantes.

### 2. Se connecter

```js
await fetch('/sanctum/csrf-cookie', { credentials: 'include' })

await fetch('/login', {
  method: 'POST',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'X-XSRF-TOKEN': decodeURIComponent(getCsrfToken()),
  },
  body: JSON.stringify({ email, password }),
})
```

Laravel crée une session et pose un cookie de session. Toutes les requêtes suivantes sont automatiquement authentifiées via ce cookie.

### 3. Règles à appliquer sur chaque requête

- `credentials: 'include'` — envoie et reçoit les cookies (équivalent de `withCredentials`)
- Header `X-XSRF-TOKEN: decodeURIComponent(getCsrfToken())` — protection CSRF

### 4. Se déconnecter

```js
await fetch('/logout', {
  method: 'POST',
  credentials: 'include',
  headers: { 'X-XSRF-TOKEN': decodeURIComponent(getCsrfToken()) },
})
```

Sanctum invalide la session côté serveur.

---

## Protéger une route

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/seances', [SeanceController::class, 'getSeances']);
    Route::post('/sessions-emargement', [EmargementController::class, 'demarrerSession']);
    // ...
});
```

Une route non protégée retourne les données à n'importe qui. Une route avec `auth:sanctum` retourne **401** si la requête n'est pas authentifiée.

---

## Tokens Bearer (Postman / tests manuels)

Pour tester des routes protégées sans passer par le cookie de session :

```php
// Créer un token (ex: dans un controller de login API)
$token = $user->createToken('postman')->plainTextToken;
// Retourne : "1|abc123..."
```

Ensuite dans Postman : header `Authorization: Bearer 1|abc123...`

Les tokens n'ont pas de date d'expiration par défaut (`'expiration' => null` dans `config/sanctum.php`). Pour en définir une :

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 jours en minutes
```

---

## Rôles (Spatie Permissions)

Trois rôles sont définis : `enseignant` , `gestionnaire` et `étudiant`. Les vérifier dans une route ou un controller :

```php
// Vérifier le rôle dans une route
Route::middleware(['auth:sanctum', 'role:enseignant'])->group(function () {
    Route::post('/sessions-emargement', ...);
});

// Ou dans un controller
if (!$request->user()->hasRole('enseignant')) {
    abort(403);
}
```

---

## Dans les tests

Sanctum fournit `Sanctum::actingAs()` pour simuler un utilisateur authentifié sans passer par le cookie ni le token :

```php
use Laravel\Sanctum\Sanctum;

// Authentifier un utilisateur avec tous les droits
Sanctum::actingAs(User::factory()->create(), ['*']);

// Authentifier un enseignant
$enseignant = User::factory()->create();
$enseignant->assignRole('enseignant');
Sanctum::actingAs($enseignant, ['*']);

$response = $this->postJson('/api/sessions-emargement', [...]);
$response->assertStatus(201);
```

---

## État actuel et TODO

| Route             | Auth actuelle                      | Attendu                            |
| ----------------- | ---------------------------------- | ---------------------------------- |
| `GET /user`       | `auth:sanctum`                     | OK                                 |
| Routes émargement | **aucune** (désactivée pour tests) | `auth:sanctum`                     |
| Routes séances    | aucune                             | `auth:sanctum`                     |
| CRUD cours        | aucune                             | `auth:sanctum` + `role:enseignant` |
| CRUD users        | aucune                             | `auth:sanctum` + role admin        |

**À faire** : réactiver `auth:sanctum` sur les routes d'émargement une fois les tests stabilisés, puis étendre progressivement aux autres routes.
