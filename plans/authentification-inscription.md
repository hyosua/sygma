# Plan : Authentification & Inscription

> PRD source : issues #25, #35, #26, #27 — Milestone [Authentification & Inscription](https://github.com/hyosua/sygma/milestone/2)

## Décisions architecturales

- **Routes backend**
  - `POST /register` — inscription formulaire
  - `GET /email/verify/{token}` — confirmation email
  - `GET /auth/google/redirect` — redirection OAuth Google
  - `GET /auth/google/callback` — callback OAuth Google
  - `POST /auth/google/finaliser` — finalisation inscription Google (choix du rôle)
  - `POST /gestionnaire/invitations` — créer une invitation gestionnaire
  - `GET /invitations/gestionnaire/{token}` — consulter une invitation
  - `POST /invitations/gestionnaire/{token}` — s'inscrire via invitation
  - `DELETE /gestionnaire/invitations/{id}` — annuler une invitation
  - `POST /gestionnaire/invitations/{id}/renvoyer` — renvoyer une invitation
  - `POST /gestionnaire/comptes/import` — import CSV/Excel

- **Schéma**
  - `users` : ajout `google_id` (string, nullable, unique) — Phase 3
  - `invitations_gestionnaire` : `id`, `email`, `token`, `expires_at`, `used_at`, `created_at` — Phase 4
  - `email_verification_tokens` ou colonne `verification_token` sur `users` — Phase 2

- **Auth**
  - Tokens Sanctum, stockés en `localStorage` côté frontend (pattern existant)
  - Rôles Spatie : `etudiant`, `enseignant`, `gestionnaire`
  - `email_verified_at` : null après inscription formulaire (rempli à la vérification), rempli automatiquement pour les inscriptions Google

- **Packages à ajouter**
  - Phase 3 : `laravel/socialite` + `socialiteproviders/google`
  - Phase 5 : `maatwebsite/excel`

---

## Phase 1 : Inscription formulaire

**Issues** : #25 (US1)
**Dépendances** : aucune

### À développer

Formulaire `/inscription` (nom, prénom, email, mot de passe, rôle) connecté à `POST /register`. Le compte est créé avec `email_verified_at` null, un token Sanctum est retourné immédiatement et l'utilisateur est redirigé vers son espace. Lien "Créer un compte" ajouté sur la page de login. L'email de confirmation sera ajouté en Phase 2 — pour l'instant l'inscription connecte directement.

### Critères d'acceptation

- [x] `POST /register` crée un compte avec le rôle `etudiant` ou `enseignant` et retourne un token Sanctum
- [x] Un email déjà utilisé retourne une erreur 409
- [x] Un rôle autre que `etudiant` / `enseignant` est refusé (422)
- [x] Page `/inscription` : formulaire fonctionnel, erreurs affichées, redirection vers l'espace utilisateur après succès
- [x] Lien "Créer un compte" visible sur la page de login et pointe vers `/inscription`
- [x] Tests feature : inscription valide (etudiant), inscription valide (enseignant), email dupliqué, rôle invalide

---

## Phase 2 : Confirmation email

**Issues** : #25 (US2, US3)
**Dépendances** : Phase 1

### À développer

On ajoute la couche email par-dessus l'inscription existante. À l'inscription, un token (UUID, TTL 24h) est généré et un email de confirmation est envoyé. L'utilisateur n'est pas encore connecté. `GET /email/verify/{token}` valide le token, marque `email_verified_at`, retourne un token Sanctum → auto-connexion. Page `/email/confirmer` affichée côté frontend pendant l'attente de vérification.

### Critères d'acceptation

- [x] L'inscription via `POST /register` envoie un email de confirmation (vérifiable via Mailpit/log)
- [x] `GET /email/verify/{token}` valide le token, remplit `email_verified_at` et retourne un token Sanctum
- [x] Token expiré (> 24h) retourne une erreur explicite
- [x] Token invalide retourne une erreur explicite
- [x] Page `/email/confirmer` affichée après inscription, en attente du clic sur le lien
- [x] Tests feature : token valide, token expiré, token invalide

---

## Phase 3 : Google OAuth (Socialite)

**Issues** : #35
**Dépendances** : Phase 1 (page `/inscription` doit exister)

### À développer

Intégration de Laravel Socialite pour Google. Trois cas dans le callback : `google_id` connu → connexion directe ; email connu → lier `google_id` + connexion directe ; utilisateur inconnu → token temporaire (UUID, TTL 5 min, stocké en cache) retourné avec `nouveau_utilisateur: true`. Le frontend redirige vers `/inscription/choisir-role` où l'utilisateur choisit son rôle. `POST /auth/google/finaliser` crée le compte avec `email_verified_at` = now(). Le rôle `gestionnaire` est refusé. Bouton "Se connecter avec Google" ajouté sur `/inscription` et sur le login.

### Critères d'acceptation

- [x] Migration : colonne `google_id` nullable unique sur `users`
- [x] `GET /auth/google/redirect` redirige vers Google
- [x] Callback — utilisateur connu par `google_id` : token Sanctum retourné directement
- [x] Callback — email connu, `google_id` null : `google_id` lié au compte, token Sanctum retourné
- [x] Callback — utilisateur inconnu : `{ nouveau_utilisateur: true, token_temporaire }` retourné
- [x] `POST /auth/google/finaliser` avec rôle `etudiant` ou `enseignant` : compte créé, `email_verified_at` rempli, token Sanctum retourné
- [x] `POST /auth/google/finaliser` avec rôle `gestionnaire` : refusé (403)
- [x] `token_temporaire` expiré : erreur explicite
- [x] Page `/inscription/choisir-role` : sélecteur de rôle fonctionnel → appel finaliser → redirection espace utilisateur
- [x] Bouton "Se connecter avec Google" visible sur `/inscription` et sur le login
- [x] Tests feature : callback connu, callback email existant, callback nouveau user, finalisation valide, finalisation rôle gestionnaire, token temporaire expiré

---

## Phase 4 : Invitation gestionnaire

**Issues** : #26
**Dépendances** : Phase 1 (logique de création de compte)

### À développer

Un gestionnaire peut inviter un autre gestionnaire via email. `POST /gestionnaire/invitations` génère un token UUID (TTL 48h) et envoie un email. `GET /invitations/gestionnaire/{token}` retourne l'email associé si le token est valide. `POST /invitations/gestionnaire/{token}` crée le compte gestionnaire (rôle verrouillé), marque le token utilisé, retourne un token Sanctum. Interface back-office gestionnaire : formulaire d'invitation + tableau avec statuts (en_attente, utilisée, expirée) + actions annuler / renvoyer.

### Critères d'acceptation

- [ ] Migration : table `invitations_gestionnaire` (`id`, `email`, `token`, `expires_at`, `used_at`, `created_at`)
- [ ] `POST /gestionnaire/invitations` crée le token et envoie l'email (protégé rôle `gestionnaire`)
- [ ] `GET /invitations/gestionnaire/{token}` retourne l'email si token valide
- [ ] `POST /invitations/gestionnaire/{token}` crée le compte gestionnaire et retourne un token Sanctum
- [ ] Token expiré ou déjà utilisé retourne une erreur explicite
- [ ] `GET /gestionnaire/invitations` retourne la liste paginée avec statuts calculés
- [ ] `DELETE /gestionnaire/invitations/{id}` annule une invitation
- [ ] `POST /gestionnaire/invitations/{id}/renvoyer` génère un nouveau token et renvoie l'email
- [ ] Page `/inscription/gestionnaire/{token}` : formulaire nom, prénom, mot de passe (email pré-rempli verrouillé) → auto-connexion
- [ ] Section back-office : formulaire invitation + tableau des invitations avec actions
- [ ] Tests feature : invitation valide, token expiré, token utilisé, email déjà existant

---

## Phase 5 : Import CSV/Excel en masse

**Issues** : #27
**Dépendances** : Phase 1 (logique de création de compte)

### À développer

Un gestionnaire peut importer un fichier CSV ou Excel pour créer plusieurs comptes `etudiant` / `enseignant` en masse. Le service de parsing lit les colonnes `nom`, `prenom`, `email`, `role`, valide chaque ligne et crée les comptes valides. Chaque compte créé reçoit un email de confirmation (Phase 2 requise pour le flow complet, sinon email ignoré). La réponse contient un rapport : nb de succès + liste des erreurs avec numéro de ligne. Un fichier entièrement invalide ne crée aucun compte.

### Critères d'acceptation

- [ ] `POST /gestionnaire/comptes/import` accepte CSV et Excel (protégé rôle `gestionnaire`)
- [ ] Colonnes attendues : `nom`, `prenom`, `email`, `role`
- [ ] Lignes avec rôle `gestionnaire` rejetées avec message explicite
- [ ] Email dupliqué : ligne rejetée, les autres lignes valides sont traitées
- [ ] Fichier entièrement invalide : aucun compte créé, erreur retournée
- [ ] Réponse : `{ succes: N, erreurs: [{ ligne, message }] }`
- [ ] Bouton "Importer CSV/Excel" dans le back-office gestionnaire
- [ ] Interface affiche le rapport après import
- [ ] Tests feature : import valide, email dupliqué, rôle gestionnaire refusé, fichier malformé
