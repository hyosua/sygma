# Documentation API — Sygma

Ce document est destiné aux développeurs frontend. Il décrit les endpoints disponibles, les paramètres attendus, les réponses retournées et les erreurs possibles. Des exemples JavaScript (`fetch`) sont fournis en fin de document.

> **Base URL** : `http://localhost:8000/api`
> **Format** : JSON
> **Authentification** : Sanctum — temporairement désactivée sur la plupart des routes pour faciliter les tests, à réactiver avant mise en production.

---

## Table des matières

- [Séances](#séances)
  - [Lister les séances](#lister-les-séances)
  - [Détail d'une séance](#détail-dune-séance)
- [Sessions d'émargement](#sessions-démargement)
  - [Démarrer une session](#démarrer-une-session)
  - [Statut d'une session](#statut-dune-session)
  - [Rafraîchir le jeton](#rafraîchir-le-jeton)
  - [Clôturer une session](#clôturer-une-session)
- [Présences](#présences)
  - [Valider via QR Code](#valider-via-qr-code)
  - [Valider manuellement](#valider-manuellement)
- [Utilisateurs](#utilisateurs)
  - [Lister tous les utilisateurs](#lister-tous-les-utilisateurs)
  - [Créer un utilisateur](#créer-un-utilisateur)
  - [Modifier un utilisateur](#modifier-un-utilisateur)
  - [Supprimer un utilisateur](#supprimer-un-utilisateur)
- [Cours](#cours)
  - [Lister les cours](#lister-les-cours)
  - [Créer un cours](#créer-un-cours)
  - [Modifier un cours](#modifier-un-cours)
  - [Supprimer un cours](#supprimer-un-cours)
- [Notes](#notes)

---

## Séances

### Lister les séances
```
GET /seances
```

**Paramètres (query string, tous optionnels)**

| Paramètre | Type | Description |
|---|---|---|
| `statut` | string | Filtre par statut : `en_cours`, `a_venir`, `terminee` |
| `enseignant_id` | integer | Filtre par enseignant |
| `groupe_id` | integer | Filtre par groupe |
| `cours_id` | integer | Filtre par cours |
| `date_debut` | datetime | Séances commençant après cette date |
| `date_fin` | datetime | Séances finissant avant cette date |
| `per_page` | integer | Nombre de résultats par page (défaut : 15, max : 50) |
| `page` | integer | Numéro de page (défaut : 1) |

**Réponse 200**
```json
{
  "data": [
    {
      "id": 61,
      "cours_id": 1,
      "enseignant_id": 67,
      "groupe_id": 1,
      "salle": null,
      "debut_a": "2026-03-12T10:41:12.000000Z",
      "fin_a": "2026-03-12T12:46:12.000000Z",
      "statut": "en_cours"
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 15,
  "total": 42,
  "next_page_url": "http://localhost:8000/api/seances?page=2",
  "prev_page_url": null
}
```

**Valeurs de `statut`**

| Valeur | Signification |
|---|---|
| `en_cours` | La séance est actuellement en cours |
| `a_venir` | La séance n'a pas encore commencé |
| `terminee` | La séance est passée |

---

### Détail d'une séance
```
GET /seances/{id}
```

**Réponse 200**
```json
{
  "id": 61,
  "cours_id": 1,
  "enseignant_id": 67,
  "groupe_id": 1,
  "salle": 12,
  "debut_a": "2026-03-12T10:41:12.000000Z",
  "fin_a": "2026-03-12T12:46:12.000000Z",
  "statut": "en_cours",
  "nombre_inscrits": 24,
  "cours": { "id": 1, "nom": "Mathématiques" },
  "enseignant": { "id": 67, "nom": "Dupont", "prenom": "Jean" },
  "groupe": { "id": 1, "nom": "G1" }
}
```

---

## Sessions d'émargement

### Démarrer une session
```
POST /sessions-emargement
```

**Corps (JSON)**

| Champ | Type | Requis | Description |
|---|---|---|---|
| `seance_id` | integer | oui | ID de la séance |
| `is_methode_qr` | boolean | oui | `true` = QR Code, `false` = manuel |
| `latitude` | float | non | Coordonnées GPS de la salle |
| `longitude` | float | non | Coordonnées GPS de la salle |

**Réponse 201**
```json
{
  "id": 5,
  "seance_id": 61,
  "is_methode_qr": true,
  "jeton": "aB3xKq...",
  "expire_a": "2026-03-12T10:42:00.000000Z",
  "latitude": null,
  "longitude": null
}
```

**Erreurs possibles**

| Code | Message | Cause |
|---|---|---|
| 500 | `La séance n'est pas en cours` | La séance n'a pas le statut `en_cours` |

---

### Statut d'une session
```
GET /sessions-emargement/{id}/statut
```

Rafraîchit automatiquement le jeton s'il est expiré (méthode QR uniquement).

**Réponse 200**
```json
{
  "id": 5,
  "jeton": "aB3xKq...",
  "expire_a": "2026-03-12T10:42:00.000000Z",
  "nombre_presents": 12
}
```

> Le jeton expire toutes les **20 secondes**. Poller cet endpoint régulièrement pour afficher le QR Code à jour.

---

### Rafraîchir le jeton
```
POST /sessions-emargement/{id}/refresh
```

Génère un nouveau jeton et repousse l'expiration de 20 secondes.

**Réponse 200** — retourne la session mise à jour (même structure que le démarrage).

---

### Clôturer une session
```
POST /sessions-emargement/{id}/cloturer
```

Met fin à la session d'émargement (expire_a = maintenant).

**Réponse 200** — retourne la session mise à jour.

---

## Présences

### Valider via QR Code
```
POST /presences/valider-qr
```

**Corps (JSON)**

| Champ | Type | Requis | Description |
|---|---|---|---|
| `jeton` | string | oui | Jeton scanné depuis le QR Code |
| `latitude` | float | non | Position GPS de l'étudiant |
| `longitude` | float | non | Position GPS de l'étudiant |

**Réponse 200**
```json
{
  "message": "Présence validée avec succès",
  "presence": {
    "id": 18,
    "session_emargement_id": 5,
    "etudiant_id": 3,
    "statut": "present",
    "scanne_a": "2026-03-12T10:41:45.000000Z"
  }
}
```

**Erreurs possibles**

| Code | Message | Cause |
|---|---|---|
| 400 | `Jeton invalide` | Le jeton n'existe pas |
| 400 | `Le QR Code a expiré, veuillez scanner le nouveau` | Le jeton a expiré |
| 400 | `La séance n'est pas active` | La séance n'est plus en cours |
| 400 | `Vous avez déjà émargé pour cette séance` | Doublon |

---

### Valider manuellement
```
POST /presences/valider-manuel
```

Réservé à l'enseignant pour marquer un étudiant présent sans QR Code.

**Corps (JSON)**

| Champ | Type | Requis | Description |
|---|---|---|---|
| `session_emargement_id` | integer | oui | ID de la session |
| `etudiant_id` | integer | oui | ID de l'étudiant |

**Réponse 200**
```json
{
  "message": "Présence validée avec succès",
  "presence": { ... }
}
```

**Erreurs possibles**

| Code | Message | Cause |
|---|---|---|
| 400 | `L'étudiant a déjà émargé pour cette session` | Doublon |
| 400 | `L'étudiant n'est pas inscrit à cette séance` | Étudiant hors groupe |

---

## Utilisateurs

### Lister tous les utilisateurs
```
GET /user/{id}
```
> Retourne tous les utilisateurs (le paramètre `{id}` est ignoré pour l'instant).

---

### Créer un utilisateur
```
POST /users/AddUser
```

**Corps (JSON)**

| Champ | Type | Description |
|---|---|---|
| `nom` | string | Nom de famille |
| `prenom` | string | Prénom |
| `email` | string | Email (doit être unique) |
| `password` | string | Mot de passe |
| `ine` | string | Numéro INE (étudiants) |
| `specialites` | string | Spécialité |
| `groupe_id` | integer | Groupe de l'étudiant |

**Réponse 200** — retourne l'utilisateur créé.
**Réponse 401** — `"Le compte existe déjà"`

---

### Modifier un utilisateur
```
PATCH /users/UpdateUser/{id}
```

**Corps (JSON)** : `nom`, `prenom`, `email`, `ine`

**Réponse 200** — retourne l'utilisateur mis à jour.
**Réponse 400** — `"Utilisateur non trouvé"`

---

### Supprimer un utilisateur
```
DELETE /users/DeleteUser/{id}
```

**Réponse 200** — `"L'utilisateur a bien été supprimé"`
**Réponse 400** — `"L'utilisateur n'a pas été trouvé"`

---

## Cours

### Lister les cours
```
GET /Cours
```
**Réponse 200** — tableau de tous les cours : `[{ "id": 1, "nom": "Mathématiques" }, ...]`

---

### Créer un cours
```
POST /Cours/Ajouter
```

**Corps (JSON)**

| Champ | Type | Requis |
|---|---|---|
| `nom` | string | oui |

**Réponse 200** — cours créé.
**Réponse 401** — `"Le cours existe déjà"`

---

### Modifier un cours
```
PATCH /Cours/Modifier/{id}
```

**Corps (JSON)** : `nom`

**Réponse 200** — cours mis à jour.
**Réponse 401** — `"Le cours existe déjà"` (nom déjà pris)
**Réponse 404** — `"cours introuvable"`

---

### Supprimer un cours
```
DELETE /Cours/Supprimer/{id}
```

**Réponse 200** — `"Le cours a bien été supprimé"`
**Réponse 404** — `"Le cours n'a pas été trouvé"`

---

## Exemples d'utilisation

### Récupérer les séances en cours

```js
const response = await fetch('http://localhost:8000/api/seances?statut=en_cours')
const data = await response.json()
console.log(data.data) // tableau de séances
```

### Naviguer entre les pages (onglet "passées")

```js
let page = 1

async function chargerSeancesPassees() {
  const response = await fetch(`http://localhost:8000/api/seances?statut=terminee&per_page=10&page=${page}`)
  const data = await response.json()

  console.log(data.data)        // séances de la page courante
  console.log(data.total)       // nombre total de séances passées
  console.log(data.last_page)   // nombre de pages disponibles

  // Bouton "Voir plus" : incrémenter page et rappeler la fonction
}
```

### Démarrer une session d'émargement (QR Code)

```js
const response = await fetch('http://localhost:8000/api/sessions-emargement', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    seance_id: 61,
    is_methode_qr: true,
  }),
})
const session = await response.json()
console.log(session.jeton)     // chaîne à encoder en QR Code
console.log(session.expire_a)  // date d'expiration du jeton
```

### Afficher le QR Code à jour (polling)

```js
async function rafraichirStatut(sessionId) {
  const response = await fetch(`http://localhost:8000/api/sessions-emargement/${sessionId}/statut`)
  const data = await response.json()

  console.log(data.jeton)           // nouveau jeton si expiré
  console.log(data.nombre_presents) // nb d'étudiants présents
}

// Appeler toutes les 20 secondes
setInterval(() => rafraichirStatut(5), 20000)
```

### Scanner un QR Code (côté étudiant)

```js
const response = await fetch('http://localhost:8000/api/presences/valider-qr', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ jeton: 'aB3xKq...' }),
})

if (!response.ok) {
  const erreur = await response.json()
  console.error(erreur.message) // ex: "Le QR Code a expiré..."
} else {
  const data = await response.json()
  console.log(data.message) // "Présence validée avec succès"
}
```

### Valider manuellement un étudiant (côté enseignant)

```js
const response = await fetch('http://localhost:8000/api/presences/valider-manuel', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    session_emargement_id: 5,
    etudiant_id: 3,
  }),
})
const data = await response.json()
console.log(data.message)
```

### Clôturer une session

```js
const response = await fetch('http://localhost:8000/api/sessions-emargement/5/cloturer', {
  method: 'POST',
})
const session = await response.json()
console.log(session.expire_a) // = maintenant
```

---

## Notes

- L'authentification Sanctum est **temporairement désactivée** sur les routes d'émargement et de séances pour faciliter les tests. Elle sera réactivée avant la mise en production.
- Les dates sont au format **ISO 8601 UTC** (`2026-03-12T10:41:12.000000Z`). Penser à convertir en heure locale côté front.
- Le jeton QR expire toutes les **20 secondes**. Prévoir un polling sur `GET /sessions-emargement/{id}/statut` pour maintenir le QR Code à jour.
