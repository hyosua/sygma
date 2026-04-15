import React from 'react';
import { NavLink } from 'react-router-dom';
import './HeaderEnseignant.css';

export default function HeaderEnseignant() {
  return (
    <header className="topbar">
      <div className="topbar-left">
        <div className="brand">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
          <span className="brand-name">SYGMA</span>
        </div>
      </div>

      <nav className="topbar-nav">
        <NavLink
          to="/enseignant/accueil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
        >
          Accueil
        </NavLink>

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
