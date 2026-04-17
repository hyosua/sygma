import { Link } from 'react-router-dom';
import './AccueilEnseignantPage.css';

export default function AccueilEnseignantPage() {
  return (
    <div className="enseignant-home-page">
      <div className="home-content">
        <section className="hero-card">
          <p className="hero-badge">Espace enseignant</p>
          <h1>Bienvenue sur votre espace</h1>
          <p className="hero-text">
            Retrouvez rapidement vos cours, gérez vos séances et accédez à vos informations
            personnelles depuis une interface simple et moderne.
          </p>
        </section>

        <section className="quick-access">
          <h2>Accès rapides</h2>

          <div className="quick-grid">
            <Link to="/enseignant/mes-cours" className="quick-card">
              <div className="quick-icon">📚</div>
              <h3>Mes cours</h3>
              <p>Consultez vos cours en cours et à venir, puis démarrez l’émargement.</p>
            </Link>

            <Link to="/enseignant/profil" className="quick-card">
              <div className="quick-icon">👤</div>
              <h3>Mon profil</h3>
              <p>Accédez à vos informations personnelles et à votre espace enseignant.</p>
            </Link>
          </div>
        </section>
      </div>
    </div>
  );
}
