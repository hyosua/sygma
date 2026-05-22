import React, { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import '../Login/LoginChoicePage.css';
import './InscriptionPage.css';

const REDIRECTIONS = {
  etudiant: '/etudiant/mes-seances',
  enseignant: '/enseignant/mes-seances',
};

export default function ChoisirRolePage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const tokenTemporaire = searchParams.get('token');

  const [role, setRole] = useState('etudiant');
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErreur(null);

    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/auth/google/finaliser`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ token_temporaire: tokenTemporaire, role }),
      });
      const data = await res.json();

      if (res.ok) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        navigate(REDIRECTIONS[role]);
      } else {
        setErreur(data.message || 'Une erreur est survenue.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setLoading(false);
    }
  };

  if (!tokenTemporaire) {
    return (
      <div className="login-choice-page">
        <div className="login-card">
          <p style={{ color: '#e53e3e' }}>
            Lien invalide. Veuillez recommencer la connexion Google.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="login-choice-page">
      <div className="login-card">
        <div className="logo-container">
          <img src="/sygma-logo.webp" alt="Logo SYGMA" className="logo-image" />
        </div>
        <h1 className="login-title">Choisir votre profil</h1>
        <p className="login-subtitle">Une dernière étape avant d'accéder à SYGMA.</p>

        <form onSubmit={handleSubmit} className="inscription-form">
          <div className="form-field">
            <label>Je suis</label>
            <div className="role-radio-group">
              {['etudiant', 'enseignant'].map((r) => (
                <label key={r} className={`role-radio-option${role === r ? ' selected' : ''}`}>
                  <input
                    type="radio"
                    name="role"
                    value={r}
                    checked={role === r}
                    onChange={() => setRole(r)}
                  />
                  {r === 'etudiant' ? 'Étudiant' : 'Enseignant'}
                </label>
              ))}
            </div>
          </div>

          {erreur && <p className="inscription-erreur">{erreur}</p>}

          <button type="submit" disabled={loading} className="role-button">
            {loading ? 'Création du compte...' : 'Accéder à SYGMA'}
          </button>
        </form>
      </div>
    </div>
  );
}
