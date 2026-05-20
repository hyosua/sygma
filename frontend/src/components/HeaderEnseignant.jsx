import React, { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './HeaderEnseignant.css';

export default function HeaderEnseignant() {
  const deconnecter = useDeconnexion();
  const [menuOuvert, setMenuOuvert] = useState(false);

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/enseignant/accueil">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
          <span className="brand-name">SYGMA</span>
        </NavLink>
      </div>

      <button
        className={`menu-toggle ${menuOuvert ? 'ouvert' : ''}`}
        onClick={() => setMenuOuvert(!menuOuvert)}
        aria-label="Menu"
      >
        <span className="hamburger-line"></span>
        <span className="hamburger-line"></span>
        <span className="hamburger-line"></span>
      </button>

      <nav className={`topbar-nav ${menuOuvert ? 'ouvert' : ''}`}>
        <NavLink
          to="/enseignant/mes-cours"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
        >
          Mes séances
        </NavLink>

        <NavLink
          to="/enseignant/archives"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
        >
          Présences
        </NavLink>

        <NavLink
          to="/enseignant/profil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
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
