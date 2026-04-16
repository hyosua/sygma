import React from 'react';
import { NavLink } from 'react-router-dom';
import './HeaderEnseignant.css';

export default function HeaderEnseignant() {
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
          Mes cours
        </NavLink>

        <NavLink
          to="/enseignant/profil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Mon profil
        </NavLink>
      </nav>
    </header>
  );
}
