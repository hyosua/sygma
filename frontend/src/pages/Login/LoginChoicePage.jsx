import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import './LoginChoicePage.css';

const ROLES = {
  enseignant: {
    label: 'Enseignant',
    email: 'enseignant@sygma.com',
    password: 'sygma',
    redirect: '/enseignant/accueil',
  },
  etudiant: {
    label: 'Étudiant',
    email: 'etudiant@sygma.com',
    password: 'sygma',
    redirect: '/etudiant/mes-seances',
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
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
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

  return (
    <div className="login-split-page">
      <div className="left-panel">
        <div className="left-content">
          <img src="/sygma-logo.webp" alt="Logo SYGMA" className="logo-image" />

          <h1>Bienvenue sur SYGMA</h1>
          <p className="brand-description">
            Gérez vos cours, vos présences et vos sessions en toute simplicité depuis une interface
            moderne, fluide et intuitive.
          </p>
        </div>
      </div>

      <div className="right-panel">
        <div className="login-card">
          {!role ? (
            <>
              <h2 className="login-title">Choisissez votre profil</h2>
              <p className="login-subtitle">Sélectionnez votre espace pour continuer.</p>

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

                <div className="divider">
                  <span>ou</span>
                </div>

                <a
                  href={`${import.meta.env.VITE_BACKEND_URL ?? ''}/auth/google/redirect`}
                  className="role-button secondary google-button"
                >
                  <img src="/google-icon.svg" alt="Google" width="18" height="18" />
                  Continuer avec Google
                </a>

                <Link to="/inscription" className="login-creer-compte">
                  Créer un compte
                </Link>

                <Link to="/demande-gestionnaire" className="login-creer-compte">
                  Devenir gestionnaire
                </Link>
              </div>
            </>
          ) : (
            <>
              <h2 className="login-title">{ROLES[role].label}</h2>
              <p className="login-subtitle">Connexion à votre espace.</p>

              <form onSubmit={handleLogin} className="login-form">
                <div className="form-group">
                  <label>Email</label>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Votre email"
                    required
                  />
                </div>

                <div className="form-group">
                  <label>Mot de passe</label>
                  <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Votre mot de passe"
                    required
                  />
                </div>

                {erreur && <p className="error-message">{erreur}</p>}

                <button type="submit" disabled={loading} className="role-button">
                  {loading ? 'Connexion...' : 'Se connecter'}
                </button>

                <button
                  type="button"
                  className="role-button secondary"
                  onClick={() => setRole(null)}
                >
                  Retour
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
