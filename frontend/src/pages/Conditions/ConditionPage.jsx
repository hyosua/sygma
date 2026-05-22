import './ConditionPage.css';

export default function ConditionsPage() {
  return (
    <div className="conditions-page">
      <section className="hero-card">
        <span className="hero-badge">SYGMA</span>

        <h1>Conditions générales d’utilisation</h1>

        <p className="hero-text">
          Veuillez lire attentivement les conditions d’utilisation de la plateforme SYGMA avant de
          créer un compte ou d’utiliser les services proposés.
        </p>
      </section>

      <section className="conditions-content">
        <h2>1. Objet</h2>

        <p>
          SYGMA est une plateforme numérique permettant la gestion des séances, des présences et de
          l’émargement des étudiants et enseignants.
        </p>

        <h2>2. Accès à la plateforme</h2>

        <p>
          L’accès à la plateforme est réservé aux utilisateurs autorisés. Chaque utilisateur est
          responsable de la confidentialité de ses identifiants de connexion.
        </p>

        <h2>3. Données personnelles</h2>

        <p>
          Les données collectées sur SYGMA sont utilisées uniquement dans le cadre de la gestion
          pédagogique et administrative des présences et des séances.
        </p>

        <h2>4. Cookies</h2>

        <p>
          La plateforme utilise des cookies afin d’assurer son bon fonctionnement, maintenir les
          sessions utilisateur et améliorer l’expérience globale.
        </p>

        <h2>5. Responsabilités</h2>

        <p>
          L’utilisateur s’engage à utiliser la plateforme de manière responsable et conforme à son
          rôle. Toute tentative de fraude ou d’utilisation abusive est interdite.
        </p>

        <h2>6. Émargement</h2>

        <p>
          L’émargement doit être effectué uniquement par l’étudiant concerné. Toute validation
          frauduleuse d’une présence pourra entraîner des sanctions.
        </p>

        <h2>7. Évolution des conditions</h2>

        <p>
          Les présentes conditions peuvent être modifiées à tout moment afin de s’adapter aux
          évolutions de la plateforme et de ses fonctionnalités.
        </p>
      </section>
    </div>
  );
}
