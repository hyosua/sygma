import React, { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './HeaderEnseignant.css';

export default function HeaderGestionnaire() {
  const deconnecter = useDeconnexion();
  const [menuOuvert, setMenuOuvert] = useState(false);

  return (
    <header className="topbar">
      <div className="topbar-left">
        <NavLink className="container-logo" to="/gestionnaire">
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
          to="/gestionnaire/presences"
          className="nav-link"
          onClick={() => setMenuOuvert(false)}
        >
          Présences
        </NavLink>
        <NavLink
          to="/gestionnaire/groupes"
          className="nav-link"
          onClick={() => setMenuOuvert(false)}
        >
          Groupes
        </NavLink>
        <NavLink
          to="/gestionnaire/invitations"
          className="nav-link"
          onClick={() => setMenuOuvert(false)}
        >
          Invitations
        </NavLink>
        <NavLink
          to="/gestionnaire/import"
          className="nav-link"
          onClick={() => setMenuOuvert(false)}
        >
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
