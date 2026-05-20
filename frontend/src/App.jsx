import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import CookieBanner from './components/CookieBanner';
import AccueilEnseignantPage from './pages/Enseignant/AccueilEnseignantPage';
import PresencesEnseignantPage from './pages/Enseignant/PresencesEnseignantPage';
import EnseignantLayout from './layouts/EnseignantLayout';
import SessionQR from './pages/Enseignant/SessionQR';
import MesCoursPage from './pages/Enseignant/MesCoursPage';
import ProfilEnseignantPage from './pages/Enseignant/ProfilEnseignantPage';
import MesCoursEtudiantPage from './pages/Etudiant/MesCoursEtudiantPage';
import ScanPresence from './pages/Etudiant/ScanPresence';
import LoginChoicePage from './pages/Login/LoginChoicePage';
import InscriptionPage from './pages/Inscription/InscriptionPage';
import InscriptionGestionnairePage from './pages/Inscription/InscriptionGestionnairePage';
import ChoisirRolePage from './pages/Inscription/ChoisirRolePage';
import EmailConfirmerPage from './pages/Email/EmailConfirmerPage';
import EmailVerifyPage from './pages/Email/EmailVerifyPage';
import GoogleSuccesPage from './pages/Auth/GoogleSuccesPage';
import GestionnaireLayout from './layouts/GestionnaireLayout';
import AccueilGestionnairePage from './pages/Gestionnaire/AccueilGestionnairePage';
import DemandeGestionnairePage from './pages/Gestionnaire/DemandeGestionnairePage';
import PresencesGestionnairePage from './pages/Gestionnaire/PresencesGestionnairePage';
import MesPresences from './pages/Etudiant/MesPresences';
import './App.css';
import './styles/variables.css';
import EtudiantLayout from './layouts/EtudiantLayout';
import ImportComptesPage from './pages/Gestionnaire/ImportComptesPage';
import GestionnaireGroupesPage from './pages/Gestionnaire/GestionnaireGroupesPage';

function App() {
  return (
    <Router>
      <Routes>
        {/* Route par défaut */}
        <Route path="/" element={<Navigate to="/login" replace />} />

        {/* Routes publiques */}
        <Route path="/login" element={<LoginChoicePage />} />
        <Route path="/inscription" element={<InscriptionPage />} />
        <Route path="/inscription/choisir-role" element={<ChoisirRolePage />} />
        <Route path="/inscription/gestionnaire/:token" element={<InscriptionGestionnairePage />} />
        <Route path="/demande-gestionnaire" element={<DemandeGestionnairePage />} />
        <Route path="/auth/google/succes" element={<GoogleSuccesPage />} />
        <Route path="/email/confirmer" element={<EmailConfirmerPage />} />
        <Route path="/email/verify/:token" element={<EmailVerifyPage />} />

        <Route path="/gestionnaire" element={<GestionnaireLayout />}>
          <Route index element={<AccueilGestionnairePage />} />
          <Route path="groupes" element={<GestionnaireGroupesPage />} />
          <Route path="import" element={<ImportComptesPage />} />
          <Route path="presences" element={<PresencesGestionnairePage />} />
        </Route>

        {/* Espace enseignant avec layout commun (Header/Sidebar) */}
        <Route path="/enseignant" element={<EnseignantLayout />}>
          <Route path="accueil" element={<AccueilEnseignantPage />} />
          <Route path="archives" element={<PresencesEnseignantPage />} />
          <Route path="mes-cours" element={<MesCoursPage />} />
          <Route path="profil" element={<ProfilEnseignantPage />} />
          <Route path="session/:seanceId" element={<SessionQR />} />
        </Route>

        {/* Route Étudiant */}
        <Route path="/etudiant" element={<EtudiantLayout />}>
          <Route path="/etudiant/scan" element={<ScanPresence />} />
          <Route path="/etudiant/mes-cours" element={<MesCoursEtudiantPage />} />
          <Route path="/etudiant/mes-presences" element={<MesPresences />} />
        </Route>
        <Route
          path="/confidentialite"
          element={<div style={{ padding: '40px' }}>Page de confidentialité à compléter</div>}
        />

        {/* Fallback 404 */}
        <Route
          path="*"
          element={
            <div style={{ padding: '2rem', textAlign: 'center' }}>
              <h1>404 - Page non trouvée</h1>
              <p>Désolé, cette page n'existe pas.</p>
            </div>
          }
        />
      </Routes>

      <CookieBanner />
    </Router>
  );
}
export default App;
