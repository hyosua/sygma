import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import SessionQR from './pages/Enseignant/SessionQR';
import MesCoursPage from './pages/Enseignant/MesCoursPage';
import MesCoursEtudiantPage from "./pages/Etudiant/MesCoursEtudiantPage";
import ScanPresence from './pages/Etudiant/ScanPresence';
import LoginChoicePage from "./pages/Login/LoginChoicePage";
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        {/* Route par défaut redirigeant vers la session QR pour le moment */}
        <Route path="/" element={<Navigate to="/login" replace />} />

        <Route path="/login" element={<LoginChoicePage />} />

        <Route path="/gestionnaire" element={<div>Page gestionnaire</div>} />

        {/* Routes Enseignant */}    
        <Route path="/enseignant/session/:seanceId" element={<SessionQR />} />
        <Route path="/enseignant/mes-cours" element={<MesCoursPage />} />

        {/* Route Étudiant */}
        <Route path="/etudiant/scan" element={<ScanPresence />} />
        <Route path="/etudiant/mes-cours" element={<MesCoursEtudiantPage />} />

        {/* Fallback route */}
        <Route path="*" element={
          <div style={{ padding: '2rem', textAlign: 'center' }}>
            <h1>404 - Page non trouvée</h1>
            <p>Désolé, cette page n'existe pas ou n'est pas encore implémentée.</p>
          </div>
        } />
      </Routes>
    </Router>
  );
}

export default App;
