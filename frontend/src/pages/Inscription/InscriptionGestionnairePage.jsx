import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import '../Login/LoginChoicePage.css';
import './InscriptionPage.css';

export default function InscriptionGestionnairePage() {
  const { token } = useParams();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [form, setForm] = useState({ nom: '', prenom: '', password: '' });
  const [erreur, setErreur] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const validerToken = async () => {
      try {
        const res = await fetch(
          `${import.meta.env.VITE_API_URL}/invitations/gestionnaire/${token}`,
          { headers: { Accept: 'application/json' } }
        );
        const data = await res.json();
        if (res.ok) {
          setEmail(data.email);
        } else {
          setErreur(data.message || "Lien d'invitation invalide ou expiré.");
        }
      } catch {
        setErreur('Impossible de contacter le serveur.');
      } finally {
        setChargement(false);
      }
    };
    validerToken();
  }, [token]);

  const handleChange = (e) => {
    setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/invitations/gestionnaire/${token}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        localStorage.setItem('token', data.token);
        navigate('/gestionnaire');
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreur(messages || "Erreur lors de l'inscription.");
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-choice-page">
      <div className="login-card">
        <div className="logo-container">
          <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />
        </div>
        <h1 className="login-title">Créer votre compte</h1>
        <p className="login-subtitle">Finalisez votre inscription en tant que gestionnaire.</p>

        {chargement && (
          <p className="inscription-erreur" style={{ color: '#666' }}>
            Vérification du lien...
          </p>
        )}

        {!chargement && erreur && !email && <p className="inscription-erreur">{erreur}</p>}

        {!chargement && email && (
          <form onSubmit={handleSubmit} className="inscription-form">
            <div className="inscription-row">
              <div className="form-field">
                <label htmlFor="prenom">Prénom</label>
                <input
                  id="prenom"
                  type="text"
                  name="prenom"
                  value={form.prenom}
                  onChange={handleChange}
                  required
                />
              </div>
              <div className="form-field">
                <label htmlFor="nom">Nom</label>
                <input
                  id="nom"
                  type="text"
                  name="nom"
                  value={form.nom}
                  onChange={handleChange}
                  required
                />
              </div>
            </div>

            <div className="form-field">
              <label htmlFor="email">Email</label>
              <input
                id="email"
                type="email"
                name="email"
                value={email}
                readOnly
                style={{ backgroundColor: '#f5f5f5', color: '#888' }}
              />
            </div>

            <div className="form-field">
              <label htmlFor="password">Mot de passe</label>
              <input
                id="password"
                type="password"
                name="password"
                value={form.password}
                onChange={handleChange}
                required
              />
            </div>

            {erreur && <p className="inscription-erreur">{erreur}</p>}

            <button type="submit" disabled={loading} className="role-button">
              {loading ? 'Inscription...' : 'Créer mon compte'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
}
