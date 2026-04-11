import { useState, useEffect, useCallback } from 'react';
import { useParams } from 'react-router-dom';
import './SessionQR.css';

const SessionQR = () => {
  const { seanceId } = useParams();
  const [seance, setSeance] = useState(null);
  const [session, setSession] = useState(null);
  const [jeton, setJeton] = useState('');
  const [jetonExpireA, setJetonExpireA] = useState(null);
  const [tempsRestant, setTempsRestant] = useState(0);
  const [nombrePresents, setNombrePresents] = useState(0);
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const token = localStorage.getItem('token');

  // Récupérer les détails de la séance à partir de l'ID
  useEffect(() => {
    const fetchSeance = async () => {
      try {
        const reponse = await fetch(`${import.meta.env.VITE_API_URL}/seances/${seanceId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        });
        const donnees = await reponse.json();
        if (reponse.ok) {
          setSeance(donnees);
        } else {
          setErreur(donnees.message || 'Erreur lors de la récupération de la séance.');
        }
      } catch (err) {
        console.error('Erreur lors de la récupération de la séance:', err);
        setErreur('Impossible de contacter le serveur.');
      }
    };

    fetchSeance();
  }, [seanceId, token]);

  // Fonction pour récupérer le statut de la session (nombre de présents, jeton, expiration)
  const fetchStatut = useCallback(async () => {
    if (!session) return;
    try {
      const reponse = await fetch(
        `${import.meta.env.VITE_API_URL}/sessions-emargement/${session.id}/statut`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        }
      );
      const donnees = await reponse.json();
      if (reponse.ok) {
        setNombrePresents(donnees.nombre_presents);
        setJeton(donnees.jeton);
        setJetonExpireA(donnees.jeton_expire_a);
      }
    } catch (err) {
      console.error('Échec de la récupération du statut', err);
    }
  }, [session, token]);

  // Calculer le temps restant chaque seconde
  useEffect(() => {
    if (!jetonExpireA || !session) return;

    const intervalleCompteur = setInterval(() => {
      const now = new Date();
      const expiration = new Date(jetonExpireA);
      const diff = Math.max(0, Math.floor((expiration - now) / 1000));

      setTempsRestant(diff);

      // Si le temps est écoulé, on rafraîchit
      if (diff === 0) {
        fetchStatut();
      }
    }, 1000);

    return () => clearInterval(intervalleCompteur);
  }, [jetonExpireA, session, fetchStatut]);

  // Fonction pour démarrer manuellement la session
  const handleStartSession = async () => {
    setLoading(true);
    setErreur(null);
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
      if (reponse.ok) {
        setSession(donnees);
        setJeton(donnees.jeton);
        setJetonExpireA(donnees.jeton_expire_a);
      } else {
        setErreur(donnees.message || 'Erreur lors du démarrage de la session.');
      }
    } catch (err) {
      console.error('Erreur lors du démarrage de la session:', err);
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setLoading(false);
    }
  };

  // Récupérer le statut de la session (nombre de présents) toutes les 5 secondes
  useEffect(() => {
    if (!session) return;

    const intervalleStatut = setInterval(fetchStatut, 5000);

    return () => clearInterval(intervalleStatut);
  }, [session, fetchStatut]);

  const handleStopSession = async () => {
    if (!session) return;
    try {
      const reponse = await fetch(
        `${import.meta.env.VITE_API_URL}/sessions-emargement/${session.id}/cloturer`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        }
      );
      if (reponse.ok) {
        setSession(null);
        setJeton('');
        // On peut aussi rediriger ou afficher un message de fin
      }
    } catch (err) {
      console.error('Échec de la clôture de la session', err);
    }
  };

  const urlQR = jeton
    ? `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(JSON.stringify({ idSession: session?.id, jeton }))}`
    : '';

  return (
    <div className="session-qr-container">
      <header className="session-header">
        <h1>Session d'Émargement en cours</h1>
        <p>Cours : {seance?.cours?.nom || 'Non spécifié'}</p>
        <p>Groupe : {seance?.groupe?.nom || 'Non spécifié'}</p>
      </header>

      <main className="session-main">
        {erreur && <div className="error-message">{erreur}</div>}

        {session ? (
          <>
            <div className="qr-card">
              <h2>Scanner pour valider votre présence</h2>
              <div className="qr-wrapper">
                {jeton ? (
                  <img src={urlQR} alt="QR Code d'émargement" className="qr-image" />
                ) : (
                  <div className="qr-placeholder">Session terminée</div>
                )}
              </div>
              {jeton && (
                <div className={`countdown-container ${tempsRestant <= 5 ? 'warning' : ''}`}>
                  <p>
                    Nouveau QR Code dans : <strong>{tempsRestant}s</strong>
                  </p>
                  <div className="progress-bar">
                    <div
                      className="progress-fill"
                      style={{ width: `${(tempsRestant / 20) * 100}%` }}
                    ></div>
                  </div>
                </div>
              )}
            </div>

            <aside className="status-card">
              <h3>Statut de la session</h3>
              <div className="stat-item">
                <span className="stat-label">Présents :</span>
                <span className="stat-value">{nombrePresents}</span>
              </div>
              <div className="stat-item">
                <span className="stat-label">Inscrits :</span>
                <span className="stat-value">{seance?.nombre_inscrits || 0}</span>
              </div>
              <button onClick={handleStopSession} className="stop-button">
                Terminer la session
              </button>
            </aside>
          </>
        ) : (
          <div className="start-session-card">
            <h2>Prêt à démarrer l'émargement ?</h2>
            <p>Cliquez sur le bouton ci-dessous pour générer le QR Code de cette séance.</p>
            <button onClick={handleStartSession} className="start-button" disabled={loading}>
              {loading ? 'Démarrage...' : 'Démarrer la session'}
            </button>
          </div>
        )}
      </main>
    </div>
  );
};

export default SessionQR;
