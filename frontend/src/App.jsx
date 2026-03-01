import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import SessionQR from './pages/Professeur/SessionQR';
import ScanPresence from './pages/Etudiant/ScanPresence';
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        {/* Route par défaut redirigeant vers la session QR pour le moment */}
        <Route path="/" element={<Navigate to="/professeur/session" replace />} />
        
        {/* Route Professeur */}
        <Route path="/professeur/session" element={<SessionQR />} />

        {/* Route Étudiant */}
        <Route path="/etudiant/scan" element={<ScanPresence />} />

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
