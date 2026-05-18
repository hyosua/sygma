import React from 'react';
import { Outlet } from 'react-router-dom';
import HeaderGestionnaire from '../components/HeaderGestionnaire';
import './GestionnaireLayout.css';

export default function GestionnaireLayout() {
  return (
    <div className="gestionnaire-layout">
      <HeaderGestionnaire />
      <main className="gestionnaire-main">
        <Outlet />
      </main>
    </div>
  );
}
