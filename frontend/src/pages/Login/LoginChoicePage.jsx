import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import './LoginChoicePage.css';

const ROLES = {
  enseignant: {
    label: 'Enseignant',
    email: 'enseignant@sygma.com',
    password: 'sygma',
    redirect: '/enseignant/mes-cours',
  },
  etudiant: {
    label: 'Étudiant',
    email: 'etudiant@sygma.com',
    password: 'sygma',
    redirect: '/etudiant/mes-cours',
  },
  gestionnaire: {
    label: 'Gestionnaire',
    email: 'admin@sygma.com',
    password: 'sygma',
    redirect: '/gestionnaire',
  },
};

export default function LoginChoicePage() {
  const navigate = useNavigate();
  const [role, setRole] = useState(null);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleRoleSelect = (selectedRole) => {
    const config = ROLES[selectedRole];
    setRole(selectedRole);
    setEmail(config.email);
    setPassword(config.password);
    setErreur(null);
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (res.ok) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        navigate(ROLES[role].redirect);
      } else {
        setErreur(data.message || 'Identifiants incorrects');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setLoading(false);
    }
  };

  if (!role) {
    return (
      <div className="login-choice-page">
        <div className="login-card">
          <div className="logo-container">
            <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />
          </div>
          <h1 className="login-title">Bienvenue sur SYGMA</h1>
          <p className="login-subtitle">Sélectionnez votre profil pour accéder à votre espace.</p>
          <div className="login-buttons">
            {Object.entries(ROLES).map(([key, config]) => (
              <button
                key={key}
                className={`role-button${key === 'gestionnaire' ? ' secondary' : ''}`}
                onClick={() => handleRoleSelect(key)}
              >
                {config.label}
              </button>
            ))}
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', margin: '4px 0' }}>
              <hr style={{ flex: 1, border: 'none', borderTop: '1px solid #ddd' }} />
              <span style={{ fontSize: '0.8rem', color: '#999' }}>ou</span>
              <hr style={{ flex: 1, border: 'none', borderTop: '1px solid #ddd' }} />
            </div>
            <a href="/auth/google/redirect" className="role-button secondary google-button">
              <img src="/google-icon.svg" alt="" width="18" height="18" />
              Continuer avec Google
            </a>
            <Link to="/inscription" className="login-creer-compte">
              Créer un compte
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="login-choice-page">
      <div className="login-card">
        <div className="logo-container">
          <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />
        </div>
        <h1 className="login-title">{ROLES[role].label}</h1>
        <p className="login-subtitle">Connexion à votre espace.</p>

        <form
          onSubmit={handleLogin}
          style={{ width: '100%', display: 'flex', flexDirection: 'column', gap: '12px' }}
        >
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
            <label style={{ fontSize: '0.85rem', color: '#666' }}>Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              style={{
                padding: '10px 12px',
                borderRadius: '8px',
                border: '1px solid #ddd',
                fontSize: '0.95rem',
              }}
            />
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
            <label style={{ fontSize: '0.85rem', color: '#666' }}>Mot de passe</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              style={{
                padding: '10px 12px',
                borderRadius: '8px',
                border: '1px solid #ddd',
                fontSize: '0.95rem',
              }}
            />
          </div>
          {erreur && <p style={{ color: '#e53e3e', fontSize: '0.9rem', margin: 0 }}>{erreur}</p>}
          <button
            type="submit"
            disabled={loading}
            className="role-button"
            style={{ marginTop: '8px' }}
          >
            {loading ? 'Connexion...' : 'Se connecter'}
          </button>
          <button type="button" className="role-button secondary" onClick={() => setRole(null)}>
            Retour
          </button>
        </form>
      </div>
    </div>
  );
}
