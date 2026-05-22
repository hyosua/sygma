import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import '../Login/LoginChoicePage.css';
import './InscriptionPage.css';

export default function InscriptionPage() {
  const navigate = useNavigate();
  const [form, setForm] = useState({
    nom: '',
    prenom: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'etudiant',
    conditions: false,
  });
  const [erreur, setErreur] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;

    setForm((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (form.password.length < 8) {
      setErreur('Le mot de passe doit contenir au moins 8 caractères.');
      return;
    }
    if (form.password !== form.confirmPassword) {
      setErreur('Les mots de passe ne correspondent pas.');
      return;
    }
    setLoading(true);
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          nom: form.nom,
          prenom: form.prenom,
          email: form.email,
          password: form.password,
          role: form.role,
        }),
      });
      const data = await res.json();
      if (res.ok) {
        navigate('/email/confirmer');
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
        <button type="button" className="inscription-retour" onClick={() => navigate(-1)}>
          ← Retour
        </button>
        <div className="logo-container">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="logo-image" />
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
            <label htmlFor="confirmPassword">Confirmer le mot de passe</label>
            <input
              id="confirmPassword"
              type="password"
              name="confirmPassword"
              value={form.confirmPassword}
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

          {/* EN cochant cette case, vous acceptez les conditions d'utilisation de SYGMA */}
          <div className="form-field checkbox-field">
            <label>
              <input
                type="checkbox"
                name="conditions"
                checked={form.conditions}
                onChange={handleChange}
                required
              />
              J'accepte les{' '}
              <a href="/conditions" target="_blank" rel="noopener noreferrer">
                conditions d'utilisation
              </a>{' '}
              de SYGMA et les cookies
            </label>
          </div>

          <button type="submit" disabled={loading || !form.conditions} className="role-button">
            {loading ? 'Inscription...' : 'Créer mon compte'}
          </button>

          <div className="inscription-separateur">
            <span>ou</span>
          </div>

          <a
            href={
              form.conditions
                ? `${import.meta.env.VITE_BACKEND_URL ?? ''}/auth/google/redirect`
                : undefined
            }
            className={`role-button secondary google-button ${
              !form.conditions ? 'disabled-button' : ''
            }`}
            onClick={(e) => {
              if (!form.conditions) {
                e.preventDefault();
              }
            }}
          >
            <img src="/google-icon.svg" alt="" width="18" height="18" />
            Continuer avec Google
          </a>

          <p className="inscription-lien-connexion">
            Déjà un compte ? <Link to="/login">Se connecter</Link>
          </p>
        </form>
      </div>
    </div>
  );
}
