import React from 'react';
import { NavLink } from 'react-router-dom';

export default function HeaderEtudiant() {
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
      </nav>
    </header>
  );
}
