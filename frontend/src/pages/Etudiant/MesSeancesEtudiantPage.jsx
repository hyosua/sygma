import React, { useEffect, useMemo, useState } from 'react';
import './MesSeancesEtudiantPage.css';
import { useNavigate } from 'react-router-dom';

const API_BASE = import.meta.env.VITE_API_URL;

function formatDate(isoString) {
  return new Date(isoString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function formatHeure(isoString) {
  return new Date(isoString).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function authHeaders() {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };
}

function SeanceCard({ seance, onEmarger }) {
  return (
    <div className="seance-card">
      <div className="seance-top">
        <div>
          <p className="seance-badge">{seance.statut === 'en_cours' ? 'En cours' : 'À venir'}</p>
          <h3 className="seance-title">{seance.nom}</h3>
        </div>
      </div>

      <div className="seance-infos">
        <div className="info-box">
          <span className="info-label">Salle</span>
          <span className="info-value">{seance.salle ?? '—'}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Classe</span>
          <span className="info-value">{seance.classe}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Professeur</span>
          <span className="info-value">{seance.professeur}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Horaire prévu</span>
          <span className="info-value">
            {seance.date} · {seance.heureDebut} - {seance.heureFin}
          </span>
        </div>
      </div>

      {seance.statut === 'en_cours' && (
        <div className="seance-actions">
          <button className="emarger-button" onClick={() => onEmarger(seance)}>
            Émarger
          </button>
        </div>
      )}
    </div>
  );
}

export default function MesSeancesEtudiantPage() {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('en_cours');
  const [seances, setSeances] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const chargerSeances = async () => {
      setLoading(true);
      try {
        const res = await fetch(`${API_BASE}/seances?par_page=50`, {
          headers: authHeaders(),
        });
        const data = await res.json();
        const liste = (data.data ?? []).map((s) => ({
          id: s.id,
          nom: s.cours?.nom ?? 'Cours inconnu',
          salle: s.salle,
          classe: s.groupe?.nom ?? '—',
          professeur: s.enseignant ? `${s.enseignant.prenom} ${s.enseignant.nom}` : '—',
          date: formatDate(s.debut_a),
          heureDebut: formatHeure(s.debut_a),
          heureFin: formatHeure(s.fin_a),
          statut: s.statut,
        }));
        setSeances(liste);
      } catch (err) {
        console.error('Erreur chargement séances', err);
      } finally {
        setLoading(false);
      }
    };
    chargerSeances();
  }, []);

  const handleEmarger = () => {
    navigate(`/etudiant/scan`);
  };

  const filteredSeances = useMemo(() => {
    return seances.filter((seance) => seance.statut === activeTab);
  }, [seances, activeTab]);

  return (
    <div className="mes-seances-page">
      <div className="overlay" />

      <div className="content">
        <header className="hero">
          <div>
            <p className="hero-tag">Espace étudiant</p>
            <h1>Mes séances</h1>
            <p className="hero-subtitle">
              Consultez vos séances en cours et à venir, avec les informations de salle, classe,
              professeur et horaire.
            </p>
          </div>
        </header>

        <section className="panel">
          <div className="tabs">
            <button
              className={`tab ${activeTab === 'en_cours' ? 'active' : ''}`}
              onClick={() => setActiveTab('en_cours')}
            >
              Séances en cours
            </button>

            <button
              className={`tab ${activeTab === 'a_venir' ? 'active' : ''}`}
              onClick={() => setActiveTab('a_venir')}
            >
              Séances à venir
            </button>
          </div>

          <div className="seances-list">
            {loading ? (
              <div className="empty-state">
                <p>Chargement de vos séances...</p>
              </div>
            ) : filteredSeances.length > 0 ? (
              filteredSeances.map((seance) => (
                <SeanceCard key={seance.id} seance={seance} onEmarger={handleEmarger} />
              ))
            ) : (
              <div className="empty-state">
                <h3>Aucune séance disponible</h3>
                <p>Il n'y a aucune séance dans cet onglet pour le moment.</p>
              </div>
            )}
          </div>
        </section>
      </div>
    </div>
  );
}
