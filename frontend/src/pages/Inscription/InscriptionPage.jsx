import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import '../Login/LoginChoicePage.css';
import './InscriptionPage.css';

const REDIRECTIONS = {
  etudiant: '/etudiant/mes-cours',
  enseignant: '/enseignant/mes-cours',
};

export default function InscriptionPage() {
  const navigate = useNavigate();
  const [form, setForm] = useState({
    nom: '',
    prenom: '',
    email: '',
    password: '',
    role: 'etudiant',
  });
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (res.ok) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        navigate(REDIRECTIONS[form.role]);
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
        <h1 className="login-title">Créer un compte</h1>
        <p className="login-subtitle">Rejoignez SYGMA en quelques secondes.</p>

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
              value={form.email}
              onChange={handleChange}
              required
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

          <div className="form-field">
            <label>Je suis</label>
            <div className="role-radio-group">
              {['etudiant', 'enseignant'].map((r) => (
                <label key={r} className={`role-radio-option${form.role === r ? ' selected' : ''}`}>
                  <input
                    type="radio"
                    name="role"
                    value={r}
                    checked={form.role === r}
                    onChange={handleChange}
                  />
                  {r === 'etudiant' ? 'Étudiant' : 'Enseignant'}
                </label>
              ))}
            </div>
          </div>

          {erreur && <p className="inscription-erreur">{erreur}</p>}

          <button type="submit" disabled={loading} className="role-button">
            {loading ? 'Inscription...' : 'Créer mon compte'}
          </button>

          <p className="inscription-lien-connexion">
            Déjà un compte ? <Link to="/login">Se connecter</Link>
          </p>
        </form>
      </div>
    </div>
  );
}
