import React from 'react';
import { Link } from 'react-router-dom';
import '../Login/LoginChoicePage.css';

export default function EmailConfirmerPage() {
  return (
    <div className="login-choice-page">
      <div className="login-card">
        <div className="logo-container">
          <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />
        </div>
        <h1 className="login-title">Vérifiez votre email</h1>
        <p className="login-subtitle">
          Un lien de confirmation a été envoyé à votre adresse email.
          <br />
          Cliquez sur ce lien pour activer votre compte.
        </p>
        <p style={{ marginTop: '1.5rem', fontSize: '0.9rem', color: '#888' }}>
          Vous ne trouvez pas l'email ? Pensez à vérifier vos spams.
        </p>
        <p style={{ marginTop: '2rem' }}>
          <Link to="/login">Retour à la connexion</Link>
        </p>
      </div>
    </div>
  );
}
