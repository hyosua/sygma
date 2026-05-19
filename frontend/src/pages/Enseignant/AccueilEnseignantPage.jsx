import { useEffect, useState } from 'react';
import './AccueilEnseignantPage.css';
import { Link, useNavigate } from 'react-router-dom';

export default function AccueilEnseignantPage() {
  const navigate = useNavigate();

  const [seancesEnCours, setSeancesEnCours] = useState([]);

  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');

  const handleStartEmargement = async (seanceId) => {
    try {
      const reponse = await fetch(`${import.meta.env.VITE_API_URL}/sessions-emargement`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          seance_id: seanceId,
          is_methode_qr: true,
        }),
      });

      const donnees = await reponse.json();

      if (!reponse.ok) {
        alert(donnees.message || 'Erreur lors du démarrage.');
        return;
      }

      navigate(`/enseignant/session/${seanceId}`, {
        state: {
          sessionDemarree: true,
          sessionData: donnees,
        },
      });
    } catch (err) {
      console.error(err);
      alert('Impossible de contacter le serveur.');
    }
  };

  useEffect(() => {
    const fetchSeances = async () => {
      try {
        const reponse = await fetch(
          `${import.meta.env.VITE_API_URL}/seances?enseignant_id=${user.id}&par_page=50`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
            },
          }
        );

        const donnees = await reponse.json();

        const seances = (donnees.data ?? []).filter((seance) => seance.statut === 'en_cours');

        setSeancesEnCours(seances);
      } catch (err) {
        console.error('Erreur lors du chargement des séances :', err);
      }
    };

    if (user.id) {
      fetchSeances();
    }
  }, [token, user.id]);

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
            {seancesEnCours.map((seance) => (
              <div key={seance.id} className="quick-card session-quick-card">
                <div className="session-card-content">
                  <span className="session-badge">Séance en cours</span>

                  <h3>{seance.cours?.nom || 'Cours non renseigné'}</h3>

                  <div className="session-infos">
                    <p>👥 {seance.groupe?.nom || 'Groupe non renseigné'}</p>
                    <p>📍 Salle {seance.salle || '—'}</p>
                  </div>
                  <button
                    className="start-emargement-button"
                    onClick={() => handleStartEmargement(seance.id)}
                  >
                    Démarrer l’émargement
                  </button>
                </div>
              </div>
            ))}

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
