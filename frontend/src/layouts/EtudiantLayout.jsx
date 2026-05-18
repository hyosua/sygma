import React from 'react';
import { NavLink } from 'react-router-dom';
import { useDeconnexion } from '../hooks/useDeconnexion';
import './EtudiantLayout.css';
import { Outlet } from 'react-router-dom';

import HeaderEtudiant from '../components/HeaderEtudiant';

export default function EtudiantLayout() {
    const deconnecter = useDeconnexion();

    return (
        <div className="etudiant-layout">
            <HeaderEtudiant />
            <main className="etudiant-main">
                <Outlet />
            </main>
        </div>
    );
}
