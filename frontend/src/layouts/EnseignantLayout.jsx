import React from 'react';
import { Outlet } from 'react-router-dom';
import HeaderEnseignant from '../components/HeaderEnseignant';
import './EnseignantLayout.css';

export default function EnseignantLayout() {
  return (
    <div className="enseignant-layout">
      <HeaderEnseignant />

      <main className="enseignant-main">
        <Outlet />
      </main>
    </div>
  );
}
