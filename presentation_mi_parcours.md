% Sygma — Avancement mi-parcours
% Emmanuelle Nsossani · Yahaya Coulibaly · Hyosua Colleter
% Avril 2026

## Présentation du projet

**Sygma** — Dématérialisation de l'émargement en école

> Remplacer les feuilles de présence papier par un système QR Code

- **3 rôles** : étudiant, enseignant, gestionnaire
- **Stack** : Laravel (API REST) + React + PostgreSQL + Docker

## Architecture générale

```
Frontend React       Backend Laravel API       Base PostgreSQL
(Vite)       ←——→   (Sanctum + Spatie)  ←——→  (PostGIS)

  Login               Controllers
  Enseignant          Services (logique métier)
  Étudiant            Exceptions custom
```

## Infrastructure ✅

- Environnement **Docker complet** — un seul `make install` pour démarrer
- **CI/CD** GitHub Actions (lint + tests automatiques)
- **Tests automatisés** sur base PostgreSQL dédiée
- Documentation API, conventions, guides de contribution

## Authentification & Rôles ✅

- Connexion / déconnexion via **Laravel Sanctum**
- Gestion des rôles avec **Spatie Permissions**
- Routes protégées par rôle (étudiant, enseignant, gestionnaire)

## Gestion des séances ✅

- CRUD complet (créer, lister, modifier, supprimer)
- Filtres avancés : statut, enseignant, groupe, cours, dates
- Pagination des résultats
- Détection de conflits de créneau et de salle
- Exceptions custom par domaine (`app/Exceptions/Seance/`)

## Émargement QR Code ✅

**Fonctionnalité cœur du projet**

1. L'enseignant ouvre une **session** → génère un jeton unique
2. Le jeton **expire automatiquement** et se renouvelle
3. L'étudiant **scanne le QR Code** → présence enregistrée
4. **Vérification géographique** via PostGIS (distance / salle)
5. **Validation manuelle** possible en cas de problème

## Suivi des absences ✅

- Récupérer les absents **du jour**
- Recherche par **date et statut**
- **Export** des données (ExportController)

## Frontend ✅

- Page de **connexion**
- Interface **enseignant** : voir ses séances, lancer une session QR
- Interface **étudiant** : s'émarger via QR Code

## Ce qu'il reste à faire 🔜

| Fonctionnalité                                    | Priorité |
| ------------------------------------------------- | -------- |
| Interface gestionnaire (tableau de bord absences) | Haute    |
| Export CSV/PDF côté frontend                      | Haute    |
| Historique présences / absences pour l'étudiant   | Moyenne  |
| Géolocalisation côté frontend                     | Moyenne  |
| Sécuriser toutes les routes (auth complète)       | Haute    |
| Notifications (absences, retards)                 | Basse    |
