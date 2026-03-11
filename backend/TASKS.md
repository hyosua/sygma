# Tâches restantes — SeanceService / SeanceController

## Fait ✅
- Migration `salle` sur la table `seances`
- Modèle `Seance` : `getStatut()`, `getStatutAttribute()`, `$appends`, `isActive()`, `salleEstOccupee()`
- Exceptions (`app/Exceptions/Seance/`) : `ConflitSeanceException`, `SalleOccupeeException`, `SeanceNonActiveException`, `SessionEmargementActiveException`
- `SeanceService` : `getSeances()` (avec filtres), `getSeance()`, `creerSeance()`, `modifierSeance()`, `verifierConflitSeance()`, `verifierConflitSalle()`, `verifierSessionEmargementActive()`
- `SeanceController` : constructeur injecté + `getSeances()`, `getSeance()`, `creerSeance()`

---

## À faire

### Modèle `Seance` — app/Models/Seance.php

- [ ] Ajouter la constante `FENETRE_EMARGEMENT_MINUTES = 120`
- [ ] Ajouter la méthode `estDansFenetreEmargement(): bool`
  → retourne true si `now()` est entre `(debut_a - 2h)` et `(fin_a + 2h)`

---

### Nouvelles exceptions — app/Exceptions/Seance/

- [ ] `SeancePasseeException` — "Impossible de démarrer une session pour une séance terminée."
- [ ] `FenetreEmargementFermeeException` — "La fenêtre d'émargement est fermée pour cette séance (±2h)."

---

### `SeanceService` — app/Services/SeanceService.php

- [ ] `getSeances()` : si filtre `enseignant_id` présent, mettre les séances **du jour en premier** (tri : séances du jour par `debut_a ASC`, puis le reste par `debut_a ASC`)
- [ ] `supprimerSeance(Seance $seance): void`
- [ ] `getFeuilleAppel(Seance $seance): Collection`
  → étudiants du groupe avec rôle 'Etudiant', triés par nom, champs : `id, nom, prenom, ine, email`
  → utiliser `$seance->groupe->users()->role('Etudiant')->...->get()`
- [ ] `genererRecurrences(array $data, string $fin_recurrence, int $frequence_jours = 7): array`
  → boucle en ajoutant `$frequence_jours` jours à chaque itération
  → mode non-bloquant : les conflits sont skippés et listés
  → retourne `['seances' => Collection, 'conflits' => array<string>, 'nb_creees' => int, 'nb_conflits' => int]`
- [ ] `getStatistiques(Seance $seance): array`
  → retourne `['nb_inscrits' => int, 'nb_presents' => int, 'taux_presence' => float]`
  → `nb_presents` = distinct etudiant_id avec statut 'present' dans les Presence liées à cette séance

---

### `SeanceController` — app/Http/Controllers/SeanceController.php

- [ ] `modifierSeance(Request $request, Seance $seance)` — PUT /seances/{seance}
  → validation `sometimes` pour tous les champs
  → catch `ConflitSeanceException` → 409, `SalleOccupeeException` → 409, `SessionEmargementActiveException` → 409
- [ ] `supprimerSeance(Seance $seance)` — DELETE /seances/{seance}
  → retourner 200 + message
- [ ] `getFeuilleAppel(Seance $seance)` — GET /seances/{seance}/feuille-appel
  → retourne `{ seance_id, nb_inscrits, etudiants: [...] }`
- [ ] `getStatistiques(Seance $seance)` — GET /seances/{seance}/statistiques
- [ ] `genererRecurrences(Request $request)` — POST /seances/recurrences
  → validation : mêmes champs que `creerSeance` + `fin_recurrence` (required|date) + `frequence_jours` (nullable|integer, défaut 7)
- [ ] `getSeancesEnseignant(User $enseignant)` — GET /enseignants/{enseignant}/seances
- [ ] `getSeancesGroupe(Groupe $groupe)` — GET /groupes/{groupe}/seances

---

### `EmargementService` — app/Services/EmargementService.php

- [ ] Mettre à jour l'import de `SeanceNonActiveException` (déplacée dans `App\Exceptions\Seance\`)
- [ ] Dans `demarrerSession()`, ajouter **avant** le `SessionEmargement::create()` :
  ```php
  if ($seance->getStatut() === 'terminee') {
      throw new SeancePasseeException();
  }
  if (!$seance->estDansFenetreEmargement()) {
      throw new FenetreEmargementFermeeException();
  }
  ```

### `EmargementController` — app/Http/Controllers/EmargementController.php

- [ ] Mettre à jour l'import de `SeanceNonActiveException` (déplacée dans `App\Exceptions\Seance\`)
- [ ] Dans `demarrerSession()`, ajouter les catch :
  ```php
  catch (SeancePasseeException $e) → 422
  catch (FenetreEmargementFermeeException $e) → 422
  ```

---

### Routes — routes/api.php

- [ ] Ajouter (attention : `POST /seances/recurrences` **doit être avant** `GET /seances/{seance}`) :
  ```
  POST   /seances                              → creerSeance
  POST   /seances/recurrences                  → genererRecurrences  ← avant {seance} !
  PUT    /seances/{seance}                     → modifierSeance
  DELETE /seances/{seance}                     → supprimerSeance
  GET    /seances/{seance}/feuille-appel       → getFeuilleAppel
  GET    /seances/{seance}/statistiques        → getStatistiques
  GET    /enseignants/{enseignant}/seances     → getSeancesEnseignant
  GET    /groupes/{groupe}/seances             → getSeancesGroupe
  ```

---

### Tests

- [ ] `tests/Feature/SeanceServiceTest.php` (à créer)
  - `getSeances` : sans filtre / par enseignant / par groupe / séances du jour en premier
  - `creerSeance` : nominal / `ConflitSeanceException` / `SalleOccupeeException` / séances consécutives OK
  - `modifierSeance` : nominal / conflit / pas de conflit avec soi-même / séance avec émargement actif
  - `supprimerSeance` : nominal
  - `genererRecurrences` : 4 semaines → 4 créées / skip conflits (mode non-bloquant)
  - `getStatistiques` : calcul taux 80% / taux 0 si aucun inscrit
  - `getFeuilleAppel` : retourne étudiants du groupe / exclut enseignants

- [ ] `tests/Feature/SeanceControllerTest.php` (compléter)
  - POST /seances : 201 / 409 conflit / 422 champs manquants
  - PUT /seances/{id} : 200 / 409 / 404
  - DELETE /seances/{id} : 200 + `assertDatabaseMissing`
  - GET /seances/{id}/feuille-appel : structure `{seance_id, nb_inscrits, etudiants}`
  - POST /seances/recurrences : 201 + structure `{seances, conflits, nb_creees}`
  - GET /enseignants/{id}/seances : séances du jour en premier

- [ ] `tests/Feature/EmargementServiceTest.php` (3 nouveaux tests)
  - `test_leve_exception_si_seance_terminee_pour_demarrer_session`
  - `test_leve_exception_si_hors_fenetre_emargement`
  - `test_accepte_si_dans_fenetre_avant_seance` (séance dans 1h → OK)
