import { useState, useEffect, useCallback } from 'react';
import { useParams, useLocation } from 'react-router-dom';
import './SessionQR.css';

const SessionQR = () => {
  const { seanceId } = useParams();
  const location = useLocation();

  const [seance, setSeance] = useState(null);
  const [session, setSession] = useState(null);
  const [jeton, setJeton] = useState('');
  const [jetonExpireA, setJetonExpireA] = useState(null);
  const [tempsRestant, setTempsRestant] = useState(0);
  const [nombrePresents, setNombrePresents] = useState(0);
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const [etudiants, setEtudiants] = useState([]);

  const [presencesValidees, setPresencesValidees] = useState({});
  const [validationEnCours, setValidationEnCours] = useState({});

  const token = localStorage.getItem('token');

  useEffect(() => {
    if (location.state?.sessionDemarree && location.state?.sessionData) {
      const sessionData = location.state.sessionData;

      setSession(sessionData);
      setJeton(sessionData.jeton);
      setJetonExpireA(sessionData.jeton_expire_a);
    }
  }, [location.state]);

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
          const groupeId = donnees?.groupe_id || donnees?.groupe?.id;
          chargerEtudiantsDuGroupe(groupeId);
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

  useEffect(() => {
    if (!jetonExpireA || !session) return;
    const intervalleCompteur = setInterval(() => {
      const now = new Date();
      const expiration = new Date(jetonExpireA);
      const diff = Math.max(0, Math.floor((expiration - now) / 1000));

      setTempsRestant(diff);

      if (diff === 0) {
        fetchStatut();
      }
    }, 1000);

    return () => clearInterval(intervalleCompteur);
  }, [jetonExpireA, session, fetchStatut]);

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

      if (!reponse.ok) {
        setErreur(donnees.message || 'Erreur lors du démarrage de la session.');
        return;
      }

      setSession(donnees);
      setJeton(donnees.jeton);
      setJetonExpireA(donnees.jeton_expire_a);

      const groupeId = seance?.groupe_id || seance?.groupe?.id;

      if (!groupeId) {
        setErreur('Impossible de récupérer le groupe de cette séance.');
        return;
      }

      const resEtudiants = await fetch(
        `${import.meta.env.VITE_API_URL}/groupes/${groupeId}/etudiants`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        }
      );

      const dataEtudiants = await resEtudiants.json();

      const users = Array.isArray(dataEtudiants) ? dataEtudiants : (dataEtudiants.data ?? []);

      setEtudiants(users);
    } catch (err) {
      console.error('Erreur lors du démarrage de la session:', err);
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setLoading(false);
    }
  };

  const handleChoixPresence = async (etudiantId) => {
    if (!session) return;

    if (presencesValidees[etudiantId] || validationEnCours[etudiantId]) {
      return;
    }

    setValidationEnCours((prev) => ({
      ...prev,
      [etudiantId]: true,
    }));

    try {
      const reponse = await fetch(`${import.meta.env.VITE_API_URL}/presences/valider-manuel`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          session_emargement_id: session.id,
          etudiant_id: etudiantId,
        }),
      });

      const donnees = await reponse.json();

      if (reponse.ok || reponse.status === 409) {
        setPresencesValidees((prev) => ({
          ...prev,
          [etudiantId]: true,
        }));

        await fetchStatut();
      } else {
        alert(donnees.message || 'Erreur lors de la validation.');
      }
    } catch (err) {
      console.error(err);
      alert('Impossible de contacter le serveur.');
    } finally {
      setValidationEnCours((prev) => ({
        ...prev,
        [etudiantId]: false,
      }));
    }
  };

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
        setJetonExpireA(null);
        setEtudiants([]);
      }
    } catch (err) {
      console.error('Échec de la clôture de la session', err);
    }
  };

  const chargerEtudiantsDuGroupe = async (groupeId) => {
    if (!groupeId) return;

    try {
      const resEtudiants = await fetch(
        `${import.meta.env.VITE_API_URL}/groupes/${groupeId}/etudiants`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        }
      );

      if (!resEtudiants.ok) return;

      const dataEtudiants = await resEtudiants.json();

      const users = Array.isArray(dataEtudiants) ? dataEtudiants : (dataEtudiants.data ?? []);

      setEtudiants(users);
    } catch (err) {
      console.error('Erreur lors du chargement des étudiants:', err);
    }
  };

  const urlQR = jeton
    ? `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(
        JSON.stringify({ idSession: session?.id, jeton })
      )}`
    : '';

  const listeEtudiants = Array.isArray(etudiants) ? etudiants : [];

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
                    />
                  </div>
                </div>
              )}
            </div>

            <div className="manual-card">
              <h2>Émargement manuel</h2>

              {listeEtudiants.length > 0 ? (
                <div className="students-list">
                  {listeEtudiants.map((etudiant) => (
                    <div key={etudiant.id} className="student-row">
                      <strong>
                        {etudiant.prenom} {etudiant.nom}
                      </strong>

                      <button
                        type="button"
                        className={`presence-present-button ${
                          presencesValidees[etudiant.id] ? 'validated' : ''
                        }`}
                        onClick={() => handleChoixPresence(etudiant.id)}
                        disabled={presencesValidees[etudiant.id] || validationEnCours[etudiant.id]}
                      >
                        {validationEnCours[etudiant.id]
                          ? 'Validation...'
                          : presencesValidees[etudiant.id]
                            ? 'Présence validée'
                            : 'Présent'}
                      </button>
                    </div>
                  ))}
                </div>
              ) : (
                <p>Aucun étudiant trouvé pour ce groupe.</p>
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
