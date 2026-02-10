# Sygma - Gestion de Présence Numérique 

## 📋 Table des matières
1. [💻 Configuration Windows (Recommandé)](#-configuration-windows-recommandé)
2. [⚡️ Premier Setup (Installation)](#️-premier-setup-installation)
3. [🛠 Session de travail quotidienne](#-session-de-travail-quotidienne)
4. [🌿 Procédure Git & Collaboration](#-procédure-git--collaboration)
5. [🌐 Accès & Commandes](#-accès--commandes)

---

## 💻 Configuration Windows (Recommandé)

Pour que le projet soit fluide (pas de lenteurs React/Laravel), suivez cet ordre :
1. Installez **Docker Desktop** avec le moteur **WSL2**.
2. **IMPORTANT** : Ne clopez pas le projet sur votre Bureau ou dans "Mes Documents". 
   - Ouvrez un terminal Ubuntu (WSL).
   - Clonez le projet dans votre `home` Linux : `cd ~ && mkdir projects && cd projects`.
   - Ouvrez ce dossier dans VS Code via l'extension "WSL".

---

## ⚡️ Premier Setup (Installation)

Une fois le projet cloné :

1. **Fichiers d'environnement** :
   Contactez [Ton Nom] pour récupérer les valeurs réelles du `.env`. Copiez-les dans `backend/.env`.

2. **Lancement automatique** :
   ```bash
   # Build et démarrage des conteneurs
   docker compose up -d --build

   # Installation automatique (Backend + Frontend)
   docker compose exec backend composer install
   docker compose exec backend php artisan key:generate
   docker compose exec backend php artisan migrate --seed
   docker compose exec frontend npm install
   ```

---

## 🛠 Session de travail quotidienne

Plus besoin de tout réinstaller ! Chaque matin, faites simplement :

- `git pull origin main` - Récupérer le travail des collègues
- `docker compose up -d` - Lancer les serveurs
- **Travaillez !** - Les changements de code sont répercutés en temps réel
- En fin de journée : `docker compose stop`

---

## 🌿 Procédure Git & Collaboration

Pour éviter de "casser" le projet des autres, respectons ce flux :

### 1. Créer une branche pour chaque tâche

```bash
git checkout -b "feat/nom-de-ta-fonctionnalite"
```

### 2. Avant de Push

Assurez-vous que votre code fonctionne et faites un dernier pull :

```bash
git pull origin main
git add .
git commit -m "Description claire de ce que j'ai fait"
git push origin feat/nom-de-ta-fonctionnalite
```

---

## 🌐 Accès & Commandes

- **Front-end** : http://localhost:3000
- **Back-end(API)** : http://localhost:8000

Besoin d'aide ? Utilisez la commande `docker compose logs -f` et envoyez une capture d'écran du message d'erreur.