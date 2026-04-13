import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import '../Login/LoginChoicePage.css';

const REDIRECTIONS = {
  etudiant: '/etudiant/mes-cours',
  enseignant: '/enseignant/mes-cours',
  gestionnaire: '/gestionnaire',
};

export default function EmailVerifyPage() {
  const { token } = useParams();
  const navigate = useNavigate();
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    const verifier = async () => {
      try {
        const res = await fetch(`${import.meta.env.VITE_API_URL}/email/verify/${token}`, {
          headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (res.ok) {
          localStorage.setItem('token', data.token);
          localStorage.setItem('user', JSON.stringify(data.user));

          const roles = data.user.roles ?? [];
          const role = roles[0]?.name ?? 'etudiant';
          navigate(REDIRECTIONS[role] ?? '/login');
        } else {
          setErreur(data.message || 'Lien invalide ou expiré.');
        }
      } catch {
        setErreur('Impossible de contacter le serveur.');
      }
    };

    verifier();
  }, [token, navigate]);

  return (
    <div className="login-choice-page">
      <div className="login-card">
        <div className="logo-container">
          <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />
        </div>
        {erreur ? (
          <>
            <h1 className="login-title">Lien invalide</h1>
            <p style={{ color: '#e55', marginTop: '1rem' }}>{erreur}</p>
            <p style={{ marginTop: '1.5rem' }}>
              <a href="/inscription">Créer un nouveau compte</a>
            </p>
          </>
        ) : (
          <>
            <h1 className="login-title">Vérification en cours…</h1>
            <p className="login-subtitle">Vous allez être redirigé automatiquement.</p>
          </>
        )}
      </div>
    </div>
  );
}
