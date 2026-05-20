import React from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './HeaderEnseignant.css';

export default function HeaderGestionnaire() {
  const deconnecter = useDeconnexion();

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/gestionnaire">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
          <span className="brand-name">SYGMA</span>
        </NavLink>
      </div>

      <nav className="topbar-nav">
        <NavLink to="/gestionnaire/presences" className="nav-link">
          Présences
        </NavLink>
        <NavLink to="/gestionnaire/groupes" className="nav-link">
          Groupes
        </NavLink>
        <NavLink to="/gestionnaire/import" className="nav-link">
          Imports
        </NavLink>
        <button
          onClick={deconnecter}
          className="nav-link"
          style={{ border: 'none', cursor: 'pointer', background: 'none' }}
        >
          Se déconnecter
        </button>
      </nav>
    </header>
  );
}
