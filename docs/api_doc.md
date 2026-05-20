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
- [Invitations gestionnaire](#invitations-gestionnaire)
  - [Inviter un gestionnaire](#inviter-un-gestionnaire)
  - [Lister les invitations](#lister-les-invitations)
  - [Annuler une invitation](#annuler-une-invitation)
  - [Renvoyer une invitation](#renvoyer-une-invitation)
  - [Vérifier un token](#vérifier-un-token)
  - [S'inscrire via token](#sinscrire-via-token)
- [Export de donner](#export-de-donner)
  - [Récupérer les sessions par date](#récupérer-les-sessions-par-date)
  - [Récupérer les absences du jour](#récupérer-les-absences-du-jour)
  - [Exporter statut par date](#exporter-statut-par-date)
- [Notes](#notes)

---

## Séances

### Lister les séances

```
GET /seances
```

**Paramètres (query string, tous optionnels)**

| Paramètre       | Type     | Description                                           |
| --------------- | -------- | ----------------------------------------------------- |
| `statut`        | string   | Filtre par statut : `en_cours`, `a_venir`, `terminee` |
| `enseignant_id` | integer  | Filtre par enseignant                                 |
| `groupe_id`     | integer  | Filtre par groupe                                     |
| `cours_id`      | integer  | Filtre par cours                                      |
| `date_debut`    | datetime | Séances commençant après cette date                   |
| `date_fin`      | datetime | Séances finissant avant cette date                    |
| `par_page`      | integer  | Nombre de résultats par page (défaut : 15, max : 50)  |
| `page`          | integer  | Numéro de page (défaut : 1)                           |

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
  "par_page": 15,
  "total": 42,
  "next_page_url": "http://localhost:8000/api/seances?page=2",
  "prev_page_url": null
}
```

**Valeurs de `statut`**

| Valeur     | Signification                       |
| ---------- | ----------------------------------- |
| `en_cours` | La séance est actuellement en cours |
| `a_venir`  | La séance n'a pas encore commencé   |
| `terminee` | La séance est passée                |

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

| Champ           | Type    | Requis | Description                        |
| --------------- | ------- | ------ | ---------------------------------- |
| `seance_id`     | integer | oui    | ID de la séance                    |
| `is_methode_qr` | boolean | oui    | `true` = QR Code, `false` = manuel |
| `latitude`      | float   | non    | Coordonnées GPS de la salle        |
| `longitude`     | float   | non    | Coordonnées GPS de la salle        |

**Réponse 201**

```json
{
  "id": 5,
  "seance_id": 61,
  "is_methode_qr": true,
  "jeton": "aB3xKq...",
  "jeton_expire_a": "2026-03-12T10:42:00.000000Z",
  "cloture_a": null,
  "latitude": null,
  "longitude": null
}
```

**Erreurs possibles**

| Code | Message                                     | Cause                                  |
| ---- | ------------------------------------------- | -------------------------------------- |
| 422  | `La séance associée n'est pas active.`      | La séance n'a pas le statut `en_cours` |

---

### Statut d'une session

```
GET /sessions-emargement/{id}/statut
```

Rafraîchit automatiquement le jeton s'il est expiré (méthode QR uniquement, session non clôturée).

**Réponse 200**

```json
{
  "id": 5,
  "jeton": "aB3xKq...",
  "jeton_expire_a": "2026-03-12T10:42:00.000000Z",
  "cloture_a": null,
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

Met fin à la session d'émargement (positionne `cloture_a` à l'heure actuelle).

**Réponse 200** — retourne la session mise à jour.

---

## Présences

### Valider via QR Code

```
POST /presences/valider-qr
```

**Corps (JSON)**

| Champ       | Type   | Requis | Description                    |
| ----------- | ------ | ------ | ------------------------------ |
| `jeton`     | string | oui    | Jeton scanné depuis le QR Code |
| `latitude`  | float  | non    | Position GPS de l'étudiant     |
| `longitude` | float  | non    | Position GPS de l'étudiant     |

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

| Code | Message                                            | Cause                         |
| ---- | -------------------------------------------------- | ----------------------------- |
| 422  | `Jeton d'émargement invalide.`                     | Le jeton n'existe pas         |
| 422  | `QR Code expiré, veuillez scanner le nouveau.`     | Le jeton a expiré             |
| 422  | `La séance associée n'est pas active.`             | La séance n'est plus en cours |
| 409  | `Vous avez déjà émargé pour cette séance.`         | Doublon                       |

---

### Valider manuellement

```
POST /presences/valider-manuel
```

Réservé à l'enseignant pour marquer un étudiant présent sans QR Code.

**Corps (JSON)**

| Champ                   | Type    | Requis | Description      |
| ----------------------- | ------- | ------ | ---------------- |
| `session_emargement_id` | integer | oui    | ID de la session |
| `etudiant_id`           | integer | oui    | ID de l'étudiant |

**Réponse 200**

```json
{
  "message": "Présence validée avec succès",
  "presence": { ... }
}
```

**Erreurs possibles**

| Code | Message                                          | Cause                |
| ---- | ------------------------------------------------ | -------------------- |
| 409  | `Vous avez déjà émargé pour cette séance.`       | Doublon              |
| 422  | `L'étudiant n'est pas inscrit à cette séance.`   | Étudiant hors groupe |

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

| Champ         | Type    | Description              |
| ------------- | ------- | ------------------------ |
| `nom`         | string  | Nom de famille           |
| `prenom`      | string  | Prénom                   |
| `email`       | string  | Email (doit être unique) |
| `password`    | string  | Mot de passe             |
| `ine`         | string  | Numéro INE (étudiants)   |
| `specialites` | string  | Spécialité               |
| `groupe_id`   | integer | Groupe de l'étudiant     |

**Réponse 201** — retourne l'utilisateur créé.
**Réponse 409** — `{"message": "Le compte existe déjà"}`

---

### Modifier un utilisateur

```
PATCH /users/UpdateUser/{id}
```

**Corps (JSON)** : `nom`, `prenom`, `email`, `ine`

**Réponse 200** — retourne l'utilisateur mis à jour.
**Réponse 404** — `{"message": "Utilisateur non trouvé"}`

---

### Supprimer un utilisateur

```
DELETE /users/DeleteUser/{id}
```

**Réponse 200** — `{"message": "L'utilisateur a bien été supprimé"}`
**Réponse 404** — `{"message": "L'utilisateur n'a pas été trouvé"}`

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
POST /cours
```

**Corps (JSON)**

| Champ | Type   | Requis |
| ----- | ------ | ------ |
| `nom` | string | oui    |

**Réponse 201** — cours créé.
**Réponse 409** — `{"message": "Le cours existe déjà"}`

---

### Modifier un cours

```
PATCH /cours/{id}
```

**Corps (JSON)** : `nom`

**Réponse 200** — cours mis à jour.
**Réponse 404** — `{"message": "cours introuvable"}`
**Réponse 409** — `{"message": "Le cours existe déjà"}` (nom déjà pris)

---

### Supprimer un cours

```
DELETE /cours/{id}
```

**Réponse 200** — `"Le cours a bien été supprimé"`
**Réponse 404** — `"Le cours n'a pas été trouvé"`

---

## Invitations gestionnaire

### Inviter un gestionnaire

```
POST /gestionnaire/invitations
```

**Auth requise** : gestionnaire

**Corps (JSON)**

| Champ   | Type   | Requis | Description                  |
| ------- | ------ | ------ | ---------------------------- |
| `email` | string | oui    | Email de la personne invitée |

**Réponse 201** — invitation créée (ou réinitialisée si l'email existait déjà).

Un email contenant un lien d'inscription valable **48 heures** est envoyé automatiquement.

**Erreurs possibles**

| Code | Cause |
| ---- | ----- |
| 422  | Email manquant ou invalide |
| 401  | Non authentifié |
| 403  | Rôle insuffisant |

---

### Lister les invitations

```
GET /gestionnaire/invitations
```

**Auth requise** : gestionnaire

**Réponse 200** — liste paginée des invitations, ordre anti-chronologique.

```json
{
  "data": [
    {
      "id": 1,
      "email": "nouveau@example.fr",
      "expires_at": "2026-04-18T09:00:00.000000Z",
      "used_at": null
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 10,
  "total": 1
}
```

---

### Annuler une invitation

```
DELETE /gestionnaire/invitations/{id}
```

**Auth requise** : gestionnaire

**Réponse 204** — aucun contenu.

---

### Renvoyer une invitation

```
POST /gestionnaire/invitations/{id}/renvoyer
```

**Auth requise** : gestionnaire

Régénère le token et la date d'expiration, renvoie l'email.

**Réponse 200**

```json
{ "message": "Invitation renvoyée." }
```

**Erreurs possibles**

| Code | Cause |
| ---- | ----- |
| 404  | Invitation introuvable |

---

### Vérifier un token

```
GET /invitations/gestionnaire/{token}
```

**Auth non requise** — utilisé par la page d'inscription frontend pour valider le lien avant affichage du formulaire.

**Réponse 200** — retourne l'invitation (dont l'email pré-rempli).

**Erreurs possibles**

| Code | Message | Cause |
| ---- | ------- | ----- |
| 422  | `Jeton invalide.` | Token inexistant |
| 422  | `Token d'invitation expiré...` | Token expiré (> 48h) |
| 422  | `Token déjà utilisé.` | Inscription déjà effectuée |

---

### S'inscrire via token

```
POST /invitations/gestionnaire/{token}
```

**Auth non requise** — point d'entrée public pour finaliser la création de compte.

**Corps (JSON)**

| Champ      | Type   | Requis | Description          |
| ---------- | ------ | ------ | -------------------- |
| `nom`      | string | oui    | Nom de famille       |
| `prenom`   | string | oui    | Prénom               |
| `password` | string | oui    | Mot de passe (≥ 8 caractères) |

**Réponse 201**

```json
{
  "message": "Inscription réussie.",
  "token": "<sanctum-token>"
}
```

Le compte est créé avec le rôle `gestionnaire`. L'invitation est marquée comme utilisée (`used_at`).

**Erreurs possibles**

| Code | Message | Cause |
| ---- | ------- | ----- |
| 422  | `Jeton invalide.` | Token inexistant |
| 422  | `Token d'invitation expiré...` | Token expiré |
| 422  | `Token déjà utilisé.` | Invitation déjà consommée |
| 422  | Erreurs de validation | Champs manquants ou mot de passe trop court |

---

## Export de donner

### Récupérer les sessions par date

```
GET /getExport
```

**Paramètres (query string)**

| Paramètre | Type   | Description                 |
| --------- | ------ | --------------------------- |
| `date`    | string | Date au format `YYYY-MM-DD` |

**Réponse 200**

```json
{
  "success": true,
  "date": "2026-03-12",
  "count": 12,
  "data": [ ... ]
}
```

---

### Récupérer les absences du jour

```
GET /getByDay
```

**Réponse 200**

```json
{
  "success": true,
  "date": "2026-03-12",
  "count": 5,
  "data": [ ... ]
}
```

---

### Exporter statut par date

```
GET /getStatutAndByDate
```

**Paramètres (query string)**

| Paramètre | Type   | Description |
| --------- | ------ | ----------- |
| `date`    | string | Date au format `YYYY-MM-DD` (optionnel, défaut : aujourd'hui) |
| `statut`  | string | Statut de présence : `present` ou `absent` |
| `type`    | string | `E` pour Excel, `P` pour PDF |

**Réponse 200**

- Télécharge un fichier Excel ou PDF selon le paramètre `type`.

**Erreurs possibles**

| Code | Message |
| ---- | ------- |
| 403  | `Aucun type cohérent choisi : choisissez E pour Excel ou P pour PDF` |

---

## Exemples d'utilisation

### Récupérer les séances en cours

```js
const response = await fetch(
  "http://localhost:8000/api/seances?statut=en_cours",
);
const data = await response.json();
console.log(data.data); // tableau de séances
```

### Naviguer entre les pages (onglet "passées")

```js
let page = 1;

async function chargerSeancesPassees() {
  const response = await fetch(
    `http://localhost:8000/api/seances?statut=terminee&par_page=10&page=${page}`,
  );
  const data = await response.json();

  console.log(data.data); // séances de la page courante
  console.log(data.total); // nombre total de séances passées
  console.log(data.last_page); // nombre de pages disponibles

  // Bouton "Voir plus" : incrémenter page et rappeler la fonction
}
```

### Démarrer une session d'émargement (QR Code)

```js
const response = await fetch("http://localhost:8000/api/sessions-emargement", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    seance_id: 61,
    is_methode_qr: true,
  }),
});
const session = await response.json();
console.log(session.jeton); // chaîne à encoder en QR Code
console.log(session.expire_a); // date d'expiration du jeton
```

### Afficher le QR Code à jour (polling)

```js
async function rafraichirStatut(sessionId) {
  const response = await fetch(
    `http://localhost:8000/api/sessions-emargement/${sessionId}/statut`,
  );
  const data = await response.json();

  console.log(data.jeton); // nouveau jeton si expiré
  console.log(data.nombre_presents); // nb d'étudiants présents
}

// Appeler toutes les 20 secondes
setInterval(() => rafraichirStatut(5), 20000);
```

### Scanner un QR Code (côté étudiant)

```js
const response = await fetch("http://localhost:8000/api/presences/valider-qr", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ jeton: "aB3xKq..." }),
});

if (!response.ok) {
  const erreur = await response.json();
  console.error(erreur.message); // ex: "Le QR Code a expiré..."
} else {
  const data = await response.json();
  console.log(data.message); // "Présence validée avec succès"
}
```

### Valider manuellement un étudiant (côté enseignant)

```js
const response = await fetch(
  "http://localhost:8000/api/presences/valider-manuel",
  {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      session_emargement_id: 5,
      etudiant_id: 3,
    }),
  },
);
const data = await response.json();
console.log(data.message);
```

### Clôturer une session

```js
const response = await fetch(
  "http://localhost:8000/api/sessions-emargement/5/cloturer",
  {
    method: "POST",
  },
);
const session = await response.json();
console.log(session.expire_a); // = maintenant
```

---

## Notes

- L'authentification Sanctum est **temporairement désactivée** sur les routes d'émargement et de séances pour faciliter les tests. Elle sera réactivée avant la mise en production.
- Les dates sont au format **ISO 8601 UTC** (`2026-03-12T10:41:12.000000Z`). Penser à convertir en heure locale côté front.
- Le jeton QR expire toutes les **20 secondes**. Prévoir un polling sur `GET /sessions-emargement/{id}/statut` pour maintenir le QR Code à jour.
