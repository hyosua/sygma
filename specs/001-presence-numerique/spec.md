# Feature Specification: Gestion de Présence Numérique

**Feature Branch**: `001-presence-numerique`
**Created**: 2026-01-07
**Status**: Draft
**Input**: User description: "Nous sommes un groupe d'étudiants voulant créer une application web (fonctionnant sur ordinateur et mobile) permettant la gestion de présence des élèves en classe. L'application a différents utilisateurs: Etudiant, Professeur, Gestionnaire(secretariat), Responsable Formation. L'idée est de valider la présence de chaque élève à l'aide de QR Code dynamique. L'application doit pouvoir être utilisée par plusieurs formations. Objectifs: Faciliter la saisies et le suivi des présences et absences, Centraliser les données de présence, automatiser les calculs (taux de présence, absence cumulées...), consultation et export des données. Fonctions: Gestion des utilisateurs, authentification, gestion des étudiants (ajout/modif/suppression, affectation à un groupe/promotion),Gestion des cours(creation/gestion des cours/seances, association enseignants/groupe étudiants), Création d'un qr code unique par séance et impossibilité de frauder. Ajout de commentaires, calcul automatique du taux de presence. Enseignant valide la feuille de presence numerique et le gestionnaire a le dernier mot avant de valider definitivement. Le gestionnaire peut avoir acces aux differentes feuilles par periodicité (semaine/mois/annees...). Un enseignant doit pouvoir visualiser le nombre d'étudiants présents durant son cours. c'est à lui de décider le début de l'émargement et la fin de l'émargement. système de notifications permettant aux gestionnaires d'être prévenus lors d'une absence d'un élève."

## User Scenarios & Testing (mandatory)

### User Story 1 - Marquage de Présence via QR Code (Priorité: P1)

Un professeur débute une session d'émargement pour son cours. Les étudiants présents scannent un QR Code dynamique pour marquer leur présence. Le professeur peut visualiser le nombre d'étudiants présents et clôturer l'émargement.

**Pourquoi cette priorité**: C'est le cœur fonctionnel de l'application, permettant la gestion de présence essentielle. Sans cela, l'application n'a pas de valeur.

**Test Indépendant**: Peut être entièrement testé par un professeur démarrant un cours et des étudiants (simulés ou réels) scannant le QR Code. Le résultat attendu est l'enregistrement correct des présences.

**Scénarios d'Acceptation**:

1.  **Étant donné** un professeur connecté et un cours planifié, **Quand** le professeur démarre l'émargement, **Alors** un QR Code dynamique est affiché pour la session.
2.  **Étant donné** un étudiant connecté et le QR Code de la session affiché, **Quand** l'étudiant scanne le QR Code, **Alors** sa présence est enregistrée pour la session.
3.  **Étant donné** un professeur ayant démarré l'émargement, **Quand** des étudiants marquent leur présence, **Alors** le professeur voit le nombre d'étudiants présents mis à jour en temps réel.
4.  **Étant donné** un professeur ayant démarré l'émargement, **Quand** le professeur clôture l'émargement, **Alors** le QR Code expire et l'enregistrement des présences est finalisé.

---

### User Story 2 - Validation et Gestion des Absences par le Gestionnaire (Priorité: P2)

Un gestionnaire accède aux feuilles de présence numériques pour consulter les absences. Il peut valider définitivement les présences/absences après la validation initiale du professeur.

**Pourquoi cette priorité**: Assure l'intégrité des données de présence et permet la gestion administrative, essentiel pour la conformité et le suivi.

**Test Indépendant**: Peut être testé par un gestionnaire accédant à une feuille de présence. Le résultat attendu est la bonne application des décisions du gestionnaire sur l'état de présence.

**Scénarios d'Acceptation**:

1.  **Étant donné** un gestionnaire connecté, **Quand** il sélectionne une période (semaine/mois/année) et une formation, **Alors** il visualise les feuilles de présence correspondantes.
2.  **Étant donné** une feuille de présence validée par un professeur, **Quand** un gestionnaire la consulte, **Alors** il peut modifier l'état de présence d'un étudiant et valider définitivement la feuille.
3.  **Étant donné** qu'un gestionnaire valide définitivement une feuille, **Alors** l'état des présences/absences est figé et les calculs automatiques sont mis à jour.

---

### User Story 3 - Marquage de Présence Manuel (Priorité: P1)

Un professeur choisit de prendre les présences manuellement. Il accède à une liste des étudiants inscrits et peut cocher chaque étudiant comme "Présent" ou "Absent".

**Pourquoi cette priorité**: Offre une alternative essentielle en cas de problème technique avec le QR Code (téléphone étudiant déchargé, problème de caméra, etc.) ou si le professeur préfère cette méthode.

**Test Indépendant**: Un professeur peut tester cette fonctionnalité de manière isolée en sélectionnant le mode manuel et en enregistrant les présences pour une liste d'étudiants simulée.

**Scénarios d'Acceptation**:

1.  **Étant donné** un professeur connecté et un cours planifié, **Quand** le professeur choisit l'option "Émargement Manuel", **Alors** la liste des étudiants inscrits à la session s'affiche.
2.  **Étant donné** la liste des étudiants affichée, **Quand** le professeur coche la case à côté du nom d'un étudiant, **Alors** l'étudiant est marqué comme "Présent".
3.  **Étant donné** la liste des étudiants, **Quand** le professeur a fini de marquer les présences, **Alors** il peut cliquer sur "Valider" pour enregistrer la feuille de présence.
4.  **Étant donné** que la feuille de présence est validée, **Alors** le système confirme que les données ont été enregistrées avec succès.

### Edge Cases

-   Que se passe-t-il si un étudiant essaie de scanner un QR Code expiré ? (Le système doit rejeter l'enregistrement)
-   Comment le système gère-t-il les déconnexions inattendues pendant l'émargement (professeur ou étudiant) ? (Les données partielles doivent être sauvegardées/récupérables, l'émargement peut être repris. Pour le mode manuel, un brouillon est sauvegardé localement.)
-   Qu'advient-il des doublons de présence (étudiant scanne plusieurs fois) ? (Seul le premier scan valide est pris en compte)
-   Comment le système empêche-t-il la fraude (partage de QR Code) ? (Une solution hybride sera utilisée, combinant des QR Codes dynamiques à validité limitée dans le temps avec une vérification par géolocalisation pour s'assurer que l'étudiant est sur le lieu du cours. Cela nécessite l'accès à la géolocalisation de l'appareil de l'étudiant.)
-   Que se passe-t-il si un gestionnaire tente de modifier une feuille de présence déjà validée définitivement ? (L'action devrait être refusée ou nécessiter une annulation/révision explicite)

## Requirements (mandatory)

### Functional Requirements

-   **FR-001**: Le système DOIT permettre la gestion complète des utilisateurs (étudiants, professeurs, gestionnaires, responsables de formation).
-   **FR-002**: Le système DOIT assurer l'authentification sécurisée de tous les types d'utilisateurs.
-   **FR-003**: Le système DOIT permettre aux gestionnaires de créer, modifier et supprimer les étudiants, ainsi que de les affecter à des groupes/promotions.
-   **FR-004**: Le système DOIT permettre aux professeurs de créer et gérer leurs cours et sessions.
-   **FR-005**: Le système DOIT permettre d'associer des enseignants à des cours et des groupes d'étudiants à des sessions.
-   **FR-006**: Le système DOIT générer un QR Code unique et dynamique pour chaque session d'émargement.
-   **FR-007**: Le système DOIT empêcher la fraude au QR Code (ex: validité temporelle limitée).
-   **FR-008**: Le système DOIT permettre aux étudiants de marquer leur présence en scannant le QR Code.
-   **FR-009**: Le système DOIT permettre aux professeurs de démarrer et de clôturer l'émargement pour une session.
-   **FR-010**: Le système DOIT permettre aux professeurs de visualiser le nombre d'étudiants présents durant son cours en temps réel.
-   **FR-011**: Le système DOIT permettre d'ajouter des commentaires aux enregistrements de présence.
-   **FR-012**: Le système DOIT calculer automatiquement le taux de présence et les absences cumulées pour chaque étudiant et formation.
-   **FR-014**: Le système DOIT permettre aux professeurs de valider une feuille de présence numérique après une session.
-   **FR-015**: Le système DOIT permettre aux gestionnaires de consulter les feuilles de présence (validées par professeurs).
-   **FR-016**: Le système DOIT permettre aux gestionnaires de valider définitivement les feuilles de présence.
-   **FR-017**: Le système DOIT permettre aux gestionnaires d'accéder aux feuilles de présence par périodicité (semaine/mois/année).
-   **FR-020**: Le système DOIT inclure un système de notifications pour avertir les gestionnaires des absences d'élèves.
-   **FR-021**: Le système DOIT centraliser toutes les données de présence.
-   **FR-022**: Le système DOIT permettre la consultation et l'exportation des données de présence et des rapports.
-   **FR-023**: Le système DOIT être accessible et fonctionnel sur les appareils de bureau et mobiles.
-   **FR-024**: Le système DOIT pouvoir être utilisé par plusieurs formations simultanément.
-   **FR-025**: Le système DOIT offrir au professeur le choix entre un émargement par QR Code ou un émargement manuel au début d'une session.
-   **FR-026**: Si le mode manuel est choisi, le système DOIT afficher la liste complète des étudiants inscrits pour la session.
-   **FR-027**: Le système DOIT permettre au professeur de marquer individuellement chaque étudiant comme "Présent" ou "Absent".
-   **FR-028**: Le système DOIT permettre la sauvegarde et la validation de la feuille de présence saisie manuellement.

### Key Entities (include if feature involves data)

-   **Utilisateur**: Représente une personne interagissant avec le système (Étudiant, Professeur, Gestionnaire, Responsable Formation). Attributs clés: ID, Nom, Prénom, Rôle, Informations de connexion.
-   **Étudiant**: Type d'Utilisateur. Attributs clés: Groupe/Promotion associé.
-   **Professeur**: Type d'Utilisateur. Attributs clés: Cours enseignés.
-   **Gestionnaire**: Type d'Utilisateur. Attributs clés: Formations gérées.
-   **Responsable Formation**: Type d'Utilisateur. Attributs clés: Formations supervisées.
-   **Formation**: Regroupement de cours et d'étudiants. Attributs clés: Nom.
-   **Cours**: Un sujet enseigné. Attributs clés: Nom, Professeur(s) associé(s), Formation associée.
-   **Session**: Une occurrence spécifique d'un cours. Attributs clés: Date, Heure de début, Heure de fin, Cours associé, Professeur ayant dispensé la session.
-   **Présence**: Enregistrement de la présence/absence d'un étudiant à une session. Attributs clés: Étudiant, Session, État (Présent, Absent), Heure de scan (pour présence), Commentaire, Validé par Professeur, Validé par Gestionnaire.
-   **QR Code Session**: Code dynamique généré pour une session. Attributs clés: ID unique, Session associée, Heure de génération, Heure d'expiration.


## Success Criteria (mandatory)

### Measurable Outcomes

-   **SC-001**: 95% des étudiants peuvent marquer leur présence via QR Code en moins de 5 secondes après l'affichage du code.
-   **SC-002**: Le temps moyen pour un professeur de démarrer et clôturer un émargement ne dépasse pas 15 secondes.
-   **SC-003**: 98% des données de présence sont enregistrées avec succès sans perte ni corruption.
-   **SC-004**: Les calculs automatiques (taux de présence, absences cumulées) sont disponibles en temps réel ou mis à jour en moins de 30 secondes après la validation d'une feuille de présence.
-   **SC-005**: 100% des notifications critiques (absences) sont envoyées et reçues dans les 2 minutes suivant l'événement déclencheur.
-   **SC-006**: Les gestionnaires peuvent accéder et exporter un rapport de présence consolidé pour une formation et une période donnée en moins de 10 secondes.
-   **SC-007**: Le taux de fraude lié à l'émargement par QR Code est inférieur à 0.1%.
-   **SC-008**: 90% des utilisateurs peuvent utiliser l'application sur mobile et desktop sans rencontrer de problèmes d'affichage ou de fonctionnalité.
-   **SC-009**: Un professeur peut enregistrer manuellement les présences pour une classe de 30 étudiants en moins de 2 minutes.