import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './MesSeancesEnseignantPage.css';

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

function SeanceCard({ seance, onStart }) {
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
        <button className="start-button" onClick={() => onStart(seance)}>
          Démarrer l'émargement
        </button>
      </div>
    </div>
  );
}

function authHeaders() {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };
}

export default function MesSeancesEnseignantPage() {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('en_cours');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [seances, setSeances] = useState([]);
  const [cours, setCours] = useState([]);
  const [groupes, setGroupes] = useState([]);
  const [erreurCreation, setErreurCreation] = useState(null);

  const defaultsFormulaire = () => {
    const maintenant = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const date = `${maintenant.getFullYear()}-${pad(maintenant.getMonth() + 1)}-${pad(maintenant.getDate())}`;
    const heureDebut = `${pad(maintenant.getHours())}:${pad(maintenant.getMinutes())}`;
    const fin = new Date(maintenant.getTime() + 4 * 60 * 60 * 1000);
    const heureFin = `${pad(fin.getHours())}:${pad(fin.getMinutes())}`;
    return {
      selectedCoursId: '',
      selectedGroupeId: '',
      nomCours: '',
      date,
      heureDebut,
      heureFin,
      salle: '',
    };
  };

  const [formData, setFormData] = useState(defaultsFormulaire);

  const user = JSON.parse(localStorage.getItem('user') || '{}');

  useEffect(() => {
    const chargerSeances = async () => {
      try {
        const res = await fetch(`${API_BASE}/seances?enseignant_id=${user.id}&par_page=50`, {
          headers: authHeaders(),
        });
        const data = await res.json();
        const liste = (data.data ?? []).map((s) => ({
          id: s.id,
          nom: s.cours?.nom ?? 'Cours inconnu',
          salle: s.salle,
          classe: s.groupe?.nom ?? '—',
          date: formatDate(s.debut_a),
          heureDebut: formatHeure(s.debut_a),
          heureFin: formatHeure(s.fin_a),
          statut: s.statut,
        }));
        setSeances(liste);
      } catch (err) {
        console.error('Erreur chargement séances', err);
      }
    };
    chargerSeances();
  }, [user.id]);

  useEffect(() => {
    if (!isModalOpen) return;
    const fetchReferentiels = async () => {
      try {
        const [resCours, resGroupes] = await Promise.all([
          fetch(`${API_BASE}/cours`, { headers: authHeaders() }),
          fetch(`${API_BASE}/groupes`, { headers: authHeaders() }),
        ]);
        setCours(await resCours.json());
        setGroupes(await resGroupes.json());
      } catch (err) {
        console.error('Erreur chargement référentiels', err);
      }
    };
    fetchReferentiels();
  }, [isModalOpen]);

  const filteredSeances = useMemo(
    () => seances.filter((s) => s.statut === activeTab),
    [seances, activeTab]
  );

  const handleStartAttendance = (seance) => {
    navigate(`/enseignant/session/${seance.id}`);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setErreurCreation(null);
    setFormData(defaultsFormulaire());
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmitSession = async (e) => {
    e.preventDefault();
    setErreurCreation(null);

    let coursId = parseInt(formData.selectedCoursId);

    if (formData.nomCours) {
      try {
        const resCours = await fetch(`${API_BASE}/cours`, {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({ nom: formData.nomCours }),
        });
        const dataCours = await resCours.json();
        if (!resCours.ok) {
          const coursExistant = cours.find(
            (c) => c.nom.toLowerCase() === formData.nomCours.toLowerCase()
          );
          if (coursExistant) {
            coursId = coursExistant.id;
          } else {
            setErreurCreation(dataCours.message || 'Erreur lors de la création du cours.');
            return;
          }
        } else {
          coursId = dataCours.id;
        }
        // Évite une double-création si la séance échoue et que l'on re-soumet
        setFormData((prev) => ({ ...prev, nomCours: '', selectedCoursId: String(coursId) }));
      } catch {
        setErreurCreation('Impossible de contacter le serveur.');
        return;
      }
    }

    // new Date('YYYY-MM-DDTHH:mm:ss') est interprété comme heure locale par le navigateur
    // → .toISOString() convertit en UTC pour que le backend (APP_TIMEZONE=UTC) stocke la bonne valeur
    const toUTC = (date, time) => new Date(`${date}T${time}:00`).toISOString().slice(0, 19) + 'Z';

    const payload = {
      cours_id: coursId,
      enseignant_id: user.id,
      groupe_id: parseInt(formData.selectedGroupeId),
      debut_a: toUTC(formData.date, formData.heureDebut),
      fin_a: toUTC(formData.date, formData.heureFin),
      salle: formData.salle ? parseInt(formData.salle) : null,
    };

    try {
      const res = await fetch(`${API_BASE}/seances`, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (res.ok) {
        handleCloseModal();
        navigate(`/enseignant/session/${data.id}`);
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreurCreation(messages || 'Erreur lors de la création de la séance.');
      }
    } catch {
      setErreurCreation('Impossible de contacter le serveur.');
    }
  };

  return (
    <div className="mes-seances-page">
      <div className="overlay" />

      <div className="content">
        <header className="hero-card">
          <div>
            {/* <p className="hero-badge">Espace enseignant</p> */}
            <h1>Mes séances</h1>
            <p className="hero-text">
              Retrouvez vos séances en cours et à venir, puis lancez rapidement l'émargement.
            </p>
          </div>
        </header>

        <section className="panel">
          <div className="tabs">
            <button
              className={`tab ${activeTab === 'en_cours' ? 'active' : ''}`}
              onClick={() => setActiveTab('en_cours')}
            >
              Séance en cours
            </button>

            <button
              className={`tab ${activeTab === 'a_venir' ? 'active' : ''}`}
              onClick={() => setActiveTab('a_venir')}
            >
              Séances à venir
            </button>
          </div>

          <div className="seances-list">
            {filteredSeances.length > 0 ? (
              filteredSeances.map((seance) => (
                <SeanceCard key={seance.id} seance={seance} onStart={handleStartAttendance} />
              ))
            ) : (
              <div className="empty-state">
                <h3>Aucune séance disponible</h3>
                <p>Il n'y a aucune séance dans cet onglet pour le moment.</p>
              </div>
            )}
          </div>
        </section>

        {isModalOpen && (
          <div className="modal-overlay" onClick={handleCloseModal}>
            <div className="modal" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h2>Créer une séance</h2>
                <button className="close-btn" onClick={handleCloseModal}>
                  ×
                </button>
              </div>

              <form className="session-form" onSubmit={handleSubmitSession}>
                <div className="form-group">
                  <label>Cours</label>
                  <select
                    name="selectedCoursId"
                    value={formData.selectedCoursId}
                    onChange={handleChange}
                    required={!formData.nomCours}
                  >
                    <option value="">Sélectionnez un cours existant…</option>
                    {cours.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.nom}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-group">
                  <label>Ou créer un nouveau cours (nom)</label>
                  <input
                    type="text"
                    name="nomCours"
                    value={formData.nomCours}
                    onChange={handleChange}
                    placeholder="Laisser vide pour utiliser le cours ci-dessus"
                    disabled={!!formData.selectedCoursId}
                  />
                </div>

                <div className="form-group">
                  <label>Groupe</label>
                  <select
                    name="selectedGroupeId"
                    value={formData.selectedGroupeId}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Sélectionnez un groupe…</option>
                    {groupes.map((g) => (
                      <option key={g.id} value={g.id}>
                        {g.nom} — {g.promotion}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-group">
                  <label>Date</label>
                  <input
                    type="date"
                    name="date"
                    value={formData.date}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label>Heure de début</label>
                    <input
                      type="time"
                      name="heureDebut"
                      value={formData.heureDebut}
                      onChange={handleChange}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Heure de fin</label>
                    <input
                      type="time"
                      name="heureFin"
                      value={formData.heureFin}
                      onChange={handleChange}
                      required
                    />
                  </div>
                </div>

                <div className="form-group">
                  <label>Numéro de salle (optionnel)</label>
                  <input
                    type="number"
                    name="salle"
                    value={formData.salle}
                    onChange={handleChange}
                    placeholder="Ex : 204"
                  />
                </div>

                {erreurCreation && (
                  <p style={{ color: '#e53e3e', fontSize: '0.9rem', margin: 0 }}>
                    {erreurCreation}
                  </p>
                )}

                <div className="form-actions">
                  <button type="button" className="secondary-btn" onClick={handleCloseModal}>
                    Annuler
                  </button>
                  <button type="submit" className="primary-btn">
                    Créer la séance
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        <button
          className="fab"
          onClick={() => {
            setFormData(defaultsFormulaire());
            setIsModalOpen(true);
          }}
        >
          +
        </button>
      </div>
    </div>
  );
}
