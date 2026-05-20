import React from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './HeaderEnseignant.css';

export default function HeaderEnseignant() {
  const deconnecter = useDeconnexion();

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/enseignant/accueil">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
          <span className="brand-name">SYGMA</span>
        </NavLink>
      </div>

      <nav className="topbar-nav">
        <NavLink
          to="/enseignant/mes-cours"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mes séances
        </NavLink>

        <NavLink
          to="/enseignant/archives"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Présences
        </NavLink>

        <NavLink
          to="/enseignant/profil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mon profil
        </NavLink>

        <button
          onClick={deconnecter}
          className="nav-link logout-button"
          style={{ border: 'none', cursor: 'pointer', background: 'none' }}
        >
          Se déconnecter
        </button>
      </nav>
    </header>
  );
}
