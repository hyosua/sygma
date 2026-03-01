import React, { useState, useEffect } from 'react';
import './SessionQR.css';

const SessionQR = () => {
    const [idSession, setIdSession] = useState('session_123'); // Simulation d'un ID de session
    const [jeton, setJeton] = useState('jeton_ABC_789'); // Simulation d'un jeton dynamique
    const [nombrePresents, setNombrePresents] = useState(0); // Simulation du nombre de présents

    // Simuler le changement de jeton toutes les 15 secondes pour la sécurité du QR Code
    useEffect(() => {
        const intervalle = setInterval(() => {
            setJeton(`jeton_${Math.random().toString(36).substring(7)}`);
        }, 15000);
        return () => clearInterval(intervalle);
    }, []);

    // Simuler l'arrivée d'étudiants
    useEffect(() => {
        const intervalle = setInterval(() => {
            setNombrePresents(prev => prev + 1);
        }, 30000); // 1 étudiant toutes les 30s
        return () => clearInterval(intervalle);
    }, []);

    const urlQR = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(JSON.stringify({ idSession, jeton }))}`;

    return (
        <div className="session-qr-container">
            <header className="session-header">
                <h1>Session d'Émargement en cours</h1>
                <p>Cours : Architecture Logicielle</p>
                <p>Groupe : M1 INFO G1</p>
            </header>

            <main className="session-main">
                <div className="qr-card">
                    <h2>Scanner pour valider votre présence</h2>
                    <div className="qr-wrapper">
                        <img src={urlQR} alt="QR Code d'émargement" className="qr-image" />
                    </div>
                    <p className="qr-expiry">Le QR Code se rafraîchit toutes les 15 secondes.</p>
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
                    <button className="stop-button">Terminer la session</button>
                </aside>
            </main>
        </div>
    );
};

export default SessionQR;
