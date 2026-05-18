import React from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';

export default function HeaderEtudiant() {
  const deconnecter = useDeconnexion();

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/etudiant/accueil">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
          <span className="brand-name">SYGMA</span>
        </NavLink>
      </div>

      <nav className="topbar-nav">
        <NavLink
          to="/etudiant/mes-cours"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mes cours
        </NavLink>

        <NavLink
          to="/etudiant/profil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mon profil
        </NavLink>
        <NavLink
          to="/etudiant/mes-presences"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mes présences
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
