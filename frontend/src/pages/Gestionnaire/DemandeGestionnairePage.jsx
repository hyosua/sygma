import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import './DemandeGestionnairePage.css';

export default function DemandeGestionnairePage() {
  const [email, setEmail] = useState('');
  const [envoi, setEnvoi] = useState(false);
  const [succes, setSucces] = useState(false);
  const [erreur, setErreur] = useState(null);

  const soumettre = async (e) => {
    e.preventDefault();
    setEnvoi(true);
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/demandes/gestionnaire`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (res.ok) {
        setSucces(true);
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreur(messages || 'Erreur lors de la demande.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setEnvoi(false);
    }
  };

  return (
    <div className="demande-page">
      <div className="demande-card">
        <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="demande-logo" />
        <h1 className="demande-titre">Demande d'accès gestionnaire</h1>

        {succes ? (
          <div className="demande-succes">
            <p>
              Votre demande a bien été envoyée. Un gestionnaire examinera votre demande et vous
              enverra un lien d'inscription par email.
            </p>
            <Link to="/login" className="demande-retour">
              Retour à la connexion
            </Link>
          </div>
        ) : (
          <>
            <p className="demande-description">
              Renseignez votre adresse email. Un gestionnaire examinera votre demande et vous
              enverra un lien d'inscription.
            </p>
            <form onSubmit={soumettre} className="demande-form">
              <input
                type="email"
                placeholder="adresse@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="demande-input"
              />
              <button type="submit" disabled={envoi} className="demande-btn">
                {envoi ? 'Envoi...' : 'Envoyer la demande'}
              </button>
            </form>
            {erreur && <p className="demande-erreur">{erreur}</p>}
            <Link to="/login" className="demande-retour">
              Retour à la connexion
            </Link>
          </>
        )}
      </div>
    </div>
  );
}
