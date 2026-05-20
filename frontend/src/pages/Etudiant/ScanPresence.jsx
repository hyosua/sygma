import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Html5Qrcode } from 'html5-qrcode';
import './ScanPresence.css';
import { useNavigate } from 'react-router-dom';

const ScanPresence = () => {
  const [statut, setStatut] = useState('chargement'); // chargement, lecture, validation, succes, erreur
  const [message, setMessage] = useState('');
  const scannerRef = useRef(null);
  const [success, setSuccess] = useState(false);
  // Ref miroir pour éviter la closure stale sur localisation
  const localisationRef = useRef(null);
  // Garde-fou : empêche handleEmargement de s'exécuter plusieurs fois
  const dejaTraiteRef = useRef(false);
  const navigate = useNavigate();

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
      const parsed = JSON.parse(donnees);
      if (parsed.jeton) jeton = parsed.jeton;
    } catch {
      console.warn('Données scannées non JSON, utilisation brute:', donnees);
    }

    try {
      const token = localStorage.getItem('token');
      const reponse = await fetch(`${import.meta.env.VITE_API_URL}/presences/valider-qr`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
        body: JSON.stringify({
          jeton: jeton,
          latitude: localisationRef.current?.latitude,
          longitude: localisationRef.current?.longitude,
        }),
      });

      const resultat = await reponse.json();

      if (reponse.ok) {
        setStatut('succes');
        setMessage(resultat.message || 'Présence validée avec succès !');

      } else {
        setStatut('erreur');
        setMessage(resultat.message || 'Erreur lors de la validation.');
        setStatut(true)
      }
    } catch (error) {
      setStatut('erreur');
      setMessage('Impossible de contacter le serveur. Vérifiez votre connexion.');
      console.error("Erreur lors de la validation de l'émargement", error);
    }
  }, []);

  useState(() => {
    setTimeout(() => {
      navigate('/etudiant/mes-cours');
    }, 5550);

  }, [true])

  useEffect(() => {
    const html5QrCode = new Html5Qrcode('reader');
    scannerRef.current = html5QrCode;
    let monte = true;

    const startScanner = async () => {
      const config = { fps: 10, qrbox: { width: 250, height: 250 } };

      try {
        await html5QrCode.start(
          { facingMode: 'environment' },
          config,
          (decodedText) => handleEmargement(decodedText),
          () => { }
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

    startScanner();

    return () => {
      monte = false;
      const stopScanner = async () => {
        if (html5QrCode.isScanning) {
          try {
            await html5QrCode.stop();
          } catch (e) {
            console.warn("Erreur lors de l'arrêt du scanner:", e);
          }
        }
        // Nettoie le DOM pour éviter le double rendu en StrictMode
        try {
          html5QrCode.clear();
        } catch (e) {
          console.warn('Erreur lors du clear du scanner:', e);
        }
      };
      stopScanner();
    };
  }, [handleEmargement]);

  return (
    <div className="scan-container">
      <header className="scan-header">
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
            <button onClick={() => window.location.reload()} className="retry-button">
              Scanner à nouveau
            </button>
          </div>
        )}

        {statut === 'erreur' && (
          <div className="result-card error">
            <div className="icon">✕</div>
            <h2>Échec</h2>
            <p>{message}</p>
            <button onClick={() => window.location.reload()} className="retry-button">
              Réessayer
            </button>
          </div>
        )}
      </main>
    </div>
  );
};

export default ScanPresence;
