import React from 'react';
import './EtudiantLayout.css';
import { Outlet } from 'react-router-dom';

import HeaderEtudiant from '../components/HeaderEtudiant';

export default function EtudiantLayout() {
  return (
    <div className="etudiant-layout">
      <HeaderEtudiant />
      <main className="etudiant-main">
        <Outlet />
      </main>
    </div>
  );
}
