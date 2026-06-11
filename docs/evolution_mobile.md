# Sygma - Perspectives d'évolution mobile

Annexe du rapport technique - à destination de l'équipe qui reprend le projet.

---

## Périmètre recommandé

L'espace étudiant est le candidat naturel pour un MVP mobile : les étudiants utilisent Sygma exclusivement sur smartphone pour deux actions - scanner un QR Code et consulter leur historique de présences. Les espaces enseignant et gestionnaire restent sur le web (interactions complexes, usage sur ordinateur).

---

## Ce qui est déjà prêt

L'architecture REST découplée a été conçue pour permettre cette évolution : le backend expose une API JSON consommée via Bearer token, sans logique de rendu. Une app mobile native peut consommer exactement la même API sans modification backend - c'est vrai pour tous les endpoints étudiants (séances, présences, émargement QR).

La documentation des endpoints est disponible dans `docs/doc_technique.md`.

---

## Points d'adaptation obligatoires

**Stockage du token.** Le frontend web utilise `localStorage` pour persister le token d'authentification. Cette API n'existe pas sur mobile natif - le token doit être stocké dans un espace sécurisé fourni par la plateforme (Keychain sur iOS, Keystore sur Android).

**Google OAuth.** Le flow actuel repose sur des redirects web vers une URL de callback. Ce mécanisme est incompatible avec une app native. Il faudra implémenter le flow PKCE avec un deep link et adapter l'endpoint `/api/auth/google/finaliser` pour accepter ce nouveau `redirect_uri`. C'est le seul point d'adaptation significatif côté backend.

**Caméra QR.** L'implémentation web actuelle (`html5-qrcode`) embarque de nombreux contournements pour iOS Safari. Sur mobile natif, les APIs caméra sont directes et fiables - tous ces contournements disparaissent. C'est le gain le plus immédiat d'une app native.

---

## Points de vigilance

**Jeton rotatif.** Le QR Code encode une URL contenant un paramètre `jeton` qui expire toutes les 20 secondes. L'app doit parser cette URL pour extraire le jeton, puis envoyer immédiatement la requête de validation avec les coordonnées GPS. La fenêtre est suffisante avec une caméra native.

**Géolocalisation - fonctionnalité incomplète.** L'anti-fraude par géolocalisation est partiellement implémentée : le frontend collecte les coordonnées GPS de l'étudiant et les envoie au backend, mais la validation côté serveur (calcul de distance entre la position de référence de la salle et la position de l'étudiant, et rejet si hors périmètre) n'est pas terminée. Il faudra compléter cette logique dans `EmargementService` avant de considérer l'anti-fraude géoloc comme opérationnelle.

**Vérification email.** L'inscription requiert une vérification par email avant la première connexion. L'app mobile doit prévoir un écran d'attente et guider l'utilisateur vers son client mail.

---

## Recommandation

Privilégier un framework cross-platform (iOS + Android avec une seule base de code). Le choix du framework est laissé à la prochaine équipe selon ses compétences - l'API REST n'impose aucune contrainte technologique côté client.
