# Guide de Contribution - Sygma

Ce document définit les règles et les standards pour assurer la qualité et la cohérence du projet.

## 🌿 Stratégie de Branches

Utiliser des préfixes pour identifier le type de travail :

- `feat/` : Nouvelle fonctionnalité (ex: `feat/generation-qr-code`)
- `fix/` : Correction de bug (ex: `fix/calcul-retard`)
- `docs/` : Documentation (ex: `docs/api-endpoints`)
- `refactor/` : Amélioration du code sans changement fonctionnel
- `test/` : Ajout ou modification de tests

**Procédure :**
1. Toujours partir de la branche `main` à jour.
```bash
git pull origin main
```
2. Créer une branche avec un nom explicite : `git checkout -b type/description-courte`.

## 🧪 Tests & Qualité

Avant chaque Pull Request, vérifiez que votre code ne casse rien :

1. **Lancer les tests** : 
   ```bash
   sygma artisan test
   ```
2. **Vérifier le style** :
   Assurez-vous qu'aucun avertissement majeur ne remonte dans vos outils de linting habituels.

## Processus de Pull Request (PR)

1. **Push** : Envoyez votre branche sur GitHub.
2. **Ouverture** : Créez la PR vers `main`.
3. **Description** : Expliquez brièvement les changements effectués.
4. **Revue (Optionnel)** : Si vous souhaitez un retour sur votre travail, demandez une revue à un collaborateur.
5. **Merge** : Une fois prêt, le merge peut être effectué.

## 🆘 Besoin d'aide ?

Si vous rencontrez un problème technique avec l'environnement Docker, utilisez la commande :
```bash
sygma repair
```
Ou consultez les logs : `docker compose logs -f`.
