import React, { useState, useEffect } from 'react';
import { Html5QrcodeScanner } from 'html5-qrcode';
import './ScanPresence.css';

const ScanPresence = () => {
    const [resultatScan, setResultatScan] = useState(null);
    const [statut, setStatut] = useState('attente'); // attente, lecture, validation, succes, erreur
    const [message, setMessage] = useState('');
    const [localisation, setLocalisation] = useState(null);

    useEffect(() => {
        // Demander la géolocalisation dès le chargement de la page
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setLocalisation({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    });
                },
                (erreur) => {
                    console.error("Erreur de géolocalisation", erreur);
                    setMessage("Attention : La géolocalisation est recommandée pour valider la présence.");
                }
            );
        }
    }, []);

    useEffect(() => {
        const lecteur = new Html5QrcodeScanner('reader', {
            qrbox: {
                width: 250,
                height: 250,
            },
            fps: 5,
        });

        const surSuccesScan = (resultat) => {
            lecteur.clear();
            setResultatScan(resultat);
            gererEmargement(resultat);
        };

        const surErreurScan = (error) => {
            // Ignorer les erreurs de scan continu
        };

        lecteur.render(surSuccesScan, surErreurScan);

        return () => {
            lecteur.clear().catch(error => console.error("Échec du nettoyage du lecteur", error));
        };
    }, []);

    const gererEmargement = async (donnees) => {
        setStatut('validation');
        setMessage('Validation de votre présence en cours...');

        try {
            // Simulation de l'appel API
            console.log("Données envoyées :", { donneesQR: donnees, localisation });
            
            // Simuler un délai réseau
            await new Promise(resoudre => setTimeout(resoudre, 2000));

            // Succès simulé
            setStatut('succes');
            setMessage('Présence validée avec succès ! Vous pouvez fermer cette page.');
        } catch (error) {
            setStatut('erreur');
            setMessage('Erreur lors de la validation. Veuillez réessayer.');
            console.log("Erreur lors de la validation de l'émargement", error);
        }
    };

    return (
        <div className="scan-container">
            <header className="scan-header">
                <h1>Émargement Étudiant</h1>
                <p>Scannez le QR Code affiché par le professeur</p>
            </header>

            <main className="scan-main">
                {(statut === 'attente' || statut === 'lecture') && (
                    <div className="scanner-wrapper">
                        <div id="reader"></div>
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
                        <button onClick={() => globalThis.location.reload()} className="retry-button">Scanner à nouveau</button>
                    </div>
                )}

                {statut === 'erreur' && (
                    <div className="result-card error">
                        <div className="icon">✕</div>
                        <h2>Échec</h2>
                        <p>{message}</p>
                        <button onClick={() => globalThis.location.reload()} className="retry-button">Réessayer</button>
                    </div>
                )}

                {message && statut === 'attente' && (
                    <div className="info-message">
                        {message}
                    </div>
                )}
            </main>
        </div>
    );
};

export default ScanPresence;
