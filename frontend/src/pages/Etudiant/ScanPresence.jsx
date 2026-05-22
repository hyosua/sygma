import React, { useState, useEffect, useLayoutEffect, useRef, useCallback } from 'react';
import { useSearchParams, useNavigate, useLocation } from 'react-router-dom';
import './ScanPresence.css';

const ScanPresence = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const jetonNatif = searchParams.get('jeton');
  const tokenRaw = localStorage.getItem('token');
  const token = tokenRaw && tokenRaw !== 'null' && tokenRaw !== 'undefined' ? tokenRaw : null;

  // Sur iOS Safari, <Navigate> ne redirige pas au chargement initial (scan natif)
  useLayoutEffect(() => {
    if (jetonNatif && !token) {
      window.location.replace('/login');
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const [statut, setStatut] = useState('chargement'); // chargement, lecture, validation, succes, erreur
  const [message, setMessage] = useState('');
  const [scanKey, setScanKey] = useState(0);
  const scannerRef = useRef(null);
  // Ref miroir pour éviter la closure stale sur localisation
  const localisationRef = useRef(null);
  // Garde-fou : empêche handleEmargement de s'exécuter plusieurs fois
  const dejaTraiteRef = useRef(false);
  const location = useLocation();

  const seance = location.state?.seance;
  // const seanceId = location.state?.seanceId;

  useEffect(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          localisationRef.current = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          };
        },
        (erreur) => {
          console.error('Erreur de géolocalisation', erreur);
          setMessage('Attention : La géolocalisation est recommandée pour valider la présence.');
        }
      );
    }
  }, []);

  const handleEmargement = useCallback(async (donnees) => {
    // Le scanner détecte en boucle — on n'exécute qu'une seule fois
    if (dejaTraiteRef.current) return;
    dejaTraiteRef.current = true;

    if (scannerRef.current && scannerRef.current.isScanning) {
      try {
        await scannerRef.current.stop();
      } catch (e) {
        console.warn("Erreur lors de l'arrêt du scanner:", e);
      }
    }

    setStatut('validation');
    setMessage('Validation de votre présence en cours...');

    let jeton = donnees;
    try {
      const url = new URL(donnees);
      jeton = url.searchParams.get('jeton') || donnees;
    } catch {
      // fallback: valeur brute du QR si ce n'est pas une URL
    }

    try {
      const authToken = localStorage.getItem('token');
      const reponse = await fetch(`${import.meta.env.VITE_API_URL}/presences/valider-qr`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          jeton: jeton,
          latitude: localisationRef.current?.latitude,
          longitude: localisationRef.current?.longitude,
        }),
      });

      const resultat = await reponse.json();

      if (reponse.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.replace('/login');
        return;
      }

      if (reponse.ok) {
        setStatut('succes');
        setMessage(resultat.message || 'Présence validée avec succès !');
      } else {
        setStatut('erreur');
        setMessage(resultat.message || 'Erreur lors de la validation.');
      }
    } catch (error) {
      setStatut('erreur');
      setMessage('Impossible de contacter le serveur. Vérifiez votre connexion.');
      console.error("Erreur lors de la validation de l'émargement", error);
    }
  }, []);

  useEffect(() => {
    if (statut !== 'succes') return;

    const timer = setTimeout(() => {
      navigate('/etudiant/mes-presences');
    }, 2000);

    return () => clearTimeout(timer);
  }, [statut, navigate]);

  // Scan natif : jeton dans l'URL + étudiant déjà connecté
  useEffect(() => {
    if (!jetonNatif || !token) return;

    handleEmargement(jetonNatif);
  }, [jetonNatif, token, handleEmargement]);

  useEffect(() => {
    if (jetonNatif) return;

    let html5QrCode = null;
    let monte = true;

    const demarrerScanner = async () => {
      const { Html5Qrcode } = await import('html5-qrcode');
      if (!monte) return;

      html5QrCode = new Html5Qrcode('reader');
      scannerRef.current = html5QrCode;

      const config = { fps: 10, qrbox: { width: 250, height: 250 } };
      try {
        await html5QrCode.start(
          { facingMode: 'environment' },
          config,
          (decodedText) => handleEmargement(decodedText),
          () => {}
        );
        if (monte) setStatut('lecture');
      } catch (err) {
        if (!monte) return;
        if (typeof err === 'string' && err.includes('already started')) {
          setStatut('lecture');
          return;
        }
        console.error('Impossible de démarrer la caméra', err);
        setStatut('erreur');
        setMessage("L'accès à la caméra a été refusé ou n'est pas disponible.");
      }
    };

    demarrerScanner();

    return () => {
      monte = false;
      if (!html5QrCode) return;
      const stopScanner = async () => {
        if (html5QrCode.isScanning) {
          try {
            await html5QrCode.stop();
          } catch (e) {
            console.warn("Erreur lors de l'arrêt du scanner:", e);
          }
        }
        try {
          html5QrCode.clear();
        } catch (e) {
          console.warn('Erreur lors du clear du scanner:', e);
        }
      };
      stopScanner();
    };
  }, [handleEmargement, jetonNatif, scanKey]);

  if (jetonNatif && !token) {
    return null;
  }

  return (
    <div className="scan-container">
      <header className="scan-header">
        {seance && (
          <div className="scan-session-card">
            <span className="scan-badge">Séance en cours</span>

            <h2>{seance.nom}</h2>

            <div className="scan-session-infos">
              <p>
                {' '}
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
                </svg>{' '}
                {seance.professeur}
              </p>
              <p>
                {' '}
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
                </svg>{' '}
                {seance.classe}
              </p>
              <p> Salle {seance.salle ?? '—'}</p>
            </div>
          </div>
        )}
        <h1>Émargement</h1>
        {statut === 'lecture' && <p>Placez le QR Code dans le cadre</p>}
      </header>

      <main className="scan-main">
        {(statut === 'chargement' || statut === 'lecture') && (
          <div className="scanner-wrapper">
            <div id="reader"></div>
            {statut === 'chargement' && (
              <div className="scanner-overlay">
                <div className="spinner"></div>
                <p>Initialisation caméra...</p>
              </div>
            )}
            {statut === 'lecture' && <div className="scan-region-highlight"></div>}
          </div>
        )}

        {statut === 'validation' && (
          <div className="loading-card">
            <div className="spinner"></div>
            <p>{message}</p>
          </div>
        )}

        {statut === 'succes' && (
          <div className="result-card success">
            <div className="icon">✓</div>
            <h2>Validé !</h2>
            <p>{message}</p>
          </div>
        )}

        {statut === 'erreur' && (
          <div className="result-card error">
            <div className="icon">✕</div>
            <h2>Échec</h2>
            <p>{message}</p>
            <button
              onClick={() => {
                dejaTraiteRef.current = false;
                setStatut('chargement');
                setMessage('');
                setScanKey((k) => k + 1);
              }}
              className="retry-button"
            >
              Réessayer
            </button>
          </div>
        )}
      </main>
    </div>
  );
};

export default ScanPresence;
