import React, { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './HeaderEnseignant.css';

export default function HeaderEtudiant() {
  const deconnecter = useDeconnexion();
  const [menuOuvert, setMenuOuvert] = useState(false);

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/etudiant/accueil">
          <img src="/sygma-logo-noir.webp" alt="Logo SYGMA" className="header-logo" />
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
          to="/etudiant/mes-seances"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
        >
          Mes séances
        </NavLink>

        <NavLink
          to="/etudiant/profil"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
        >
          Mon profil
        </NavLink>
        <NavLink
          to="/etudiant/mes-presences"
          className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}
          onClick={() => setMenuOuvert(false)}
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
