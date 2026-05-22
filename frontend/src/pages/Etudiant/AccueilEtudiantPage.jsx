import { useEffect, useState } from 'react';
import './AccueilEtudiantPage.css';
import { Link, useNavigate } from 'react-router-dom';

export default function AccueilEtudiantPage() {
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
          <p className="hero-badge">Espace étudiant</p>
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
                    <p>
                      <svg
                        viewBox="0 0 24 24"
                        width="16"
                        height="16"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="info-icon"
                      >
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                      </svg>
                      {seance.groupe?.nom || 'Groupe non renseigné'}
                    </p>
                    <p>
                      <svg
                        viewBox="0 0 24 24"
                        width="16"
                        height="16"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="info-icon"
                      >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                      </svg>
                      Salle {seance.salle || '—'}
                    </p>
                  </div>
                  <button
                    className="start-emargement-button"
                    onClick={() => handleStartEmargement(seance.id)}
                  >
                    Emarger
                  </button>
                </div>
              </div>
            ))}

            <Link to="/enseignant/profil" className="quick-card">
              <div className="quick-icon">
                <svg
                  viewBox="0 0 24 24"
                  width="24"
                  height="24"
                  stroke="#451ED0"
                  strokeWidth="2"
                  fill="none"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
              </div>

              <h3>Mon profil</h3>

              <p>Accédez à vos informations personnelles et à votre espace enseignant.</p>
            </Link>
          </div>
        </section>
      </div>
    </div>
  );
}
