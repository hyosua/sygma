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
          <h3 className="seance-title">{seance.nom}</h3>
        </div>
      </div>

      <div className="seance-infos">
        <p>
          {' '}
          <svg
            viewBox="0 0 24 24"
            width="16"
            height="16"
            stroke="currentColor"
            strokeWidth="2"
            fill="none"
            strokeLinecap="round"
            strokeLinejoin="round"
            className="info-icon"
          >
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>{' '}
          {seance.classe}
        </p>
        <p>
          {' '}
          <svg
            viewBox="0 0 24 24"
            width="16"
            height="16"
            stroke="currentColor"
            strokeWidth="2"
            fill="none"
            strokeLinecap="round"
            strokeLinejoin="round"
            className="info-icon"
          >
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>{' '}
          Salle {seance.salle ?? '—'}
        </p>
        <p>
          {seance.date} · {seance.heureDebut} - {seance.heureFin}
        </p>
      </div>

      <div className="seance-actions">
        {seance.emarge ? (
          <span className="emarge-badge">Émargée</span>
        ) : (
          <button className="start-button" onClick={() => onEmarger(seance)}>
            Émarger
          </button>
        )}
      </div>
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
          emarge: s.emarge ?? false,
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

  const handleEmarger = (seance) => {
    navigate(`/etudiant/scan`, {
      state: {
        seanceId: seance.id,
        seance,
      },
    });
  };

  const filteredSeances = useMemo(() => {
    return seances.filter((seance) => seance.statut === activeTab);
  }, [seances, activeTab]);

  return (
    <div className="mes-seances-page">
      <div className="overlay" />

      <div className="content">
        <header className="hero-card">
          <div>
            <h1>Mes séances</h1>
            <p className="hero-text">
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
