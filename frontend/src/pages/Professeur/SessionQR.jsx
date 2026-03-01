import React, { useState, useEffect } from 'react';
import './SessionQR.css';

const SessionQR = ({ seance_id = 10 }) => { // ID par défaut pour test
    const [session, setSession] = useState(null);
    const [jeton, setJeton] = useState('');
    const [nombrePresents, setNombrePresents] = useState(0);
    const [erreur, setErreur] = useState(null);
    const [enChargement, setEnChargement] = useState(false);

    const token = localStorage.getItem('token'); 

    // Fonction pour démarrer manuellement la session
    const handleStartSession = async () => {
        setEnChargement(true);
        setErreur(null);
        try {
            const reponse = await fetch('http://localhost:8000/api/sessions-emargement', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    seance_id: seance_id,
                    methode: 'qr',
                }),
            });
            const donnees = await reponse.json();
            if (reponse.ok) {
                setSession(donnees);
                setJeton(donnees.jeton);
            } else {
                setErreur(donnees.message || "Erreur lors du démarrage de la session.");
            }
        } catch (err) {
            setErreur("Impossible de contacter le serveur.");
        } finally {
            setEnChargement(false);
        }
    };

    // Récupérer le statut de la session (incluant le jeton actuel) toutes les 5 secondes
    useEffect(() => {
        if (!session) return;

        const intervalle = setInterval(async () => {
            try {
                const reponse = await fetch(`http://localhost:8000/api/sessions-emargement/${session.id}/status`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                });
                const donnees = await reponse.json();
                if (reponse.ok) {
                    setNombrePresents(donnees.nombre_presents);
                    setJeton(donnees.jeton); // Le jeton est rafraîchi par le back si besoin
                }
            } catch (err) {
                console.error("Échec de la récupération du statut", err);
            }
        }, 5000);

        return () => clearInterval(intervalle);
    }, [session, token]);

    const handleStopSession = async () => {
        if (!session) return;
        try {
            const reponse = await fetch(`http://localhost:8000/api/sessions-emargement/${session.id}/cloturer`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            if (reponse.ok) {
                setSession(null);
                setJeton('');
                // On peut aussi rediriger ou afficher un message de fin
            }
        } catch (err) {
            console.error("Échec de la clôture de la session", err);
        }
    };

    const urlQR = jeton 
        ? `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(JSON.stringify({ idSession: session?.id, jeton }))}`
        : '';

    return (
        <div className="session-qr-container">
            <header className="session-header">
                <h1>Session d'Émargement en cours</h1>
                <p>Cours : Architecture Logicielle</p>
                <p>Groupe : M1 INFO G1</p>
            </header>

            <main className="session-main">
                {erreur && <div className="error-message">{erreur}</div>}
                
                {!session ? (
                    <div className="start-session-card">
                        <h2>Prêt à démarrer l'émargement ?</h2>
                        <p>Cliquez sur le bouton ci-dessous pour générer le QR Code de cette séance.</p>
                        <button 
                            onClick={handleStartSession} 
                            className="start-button"
                            disabled={enChargement}
                        >
                            {enChargement ? 'Démarrage...' : 'Démarrer la session'}
                        </button>
                    </div>
                ) : (
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
                            {jeton && <p className="qr-expiry">Le QR Code se rafraîchit automatiquement.</p>}
                        </div>

                        <aside className="status-card">
                            <h3>Statut de la session</h3>
                            <div className="stat-item">
                                <span className="stat-label">Présents :</span>
                                <span className="stat-value">{nombrePresents}</span>
                            </div>
                            <div className="stat-item">
                                <span className="stat-label">Inscrits :</span>
                                <span className="stat-value">25</span>
                            </div>
                            <button onClick={handleStopSession} className="stop-button">Terminer la session</button>
                        </aside>
                    </>
                )}
            </main>
        </div>
    );
};

export default SessionQR;
