import React, { useEffect, useState } from 'react';
import '../Gestionnaire/PresencesGestionnairePage.css';
import './PresencesEnseignantPage.css';

const STATUTS = [
  { valeur: '', label: 'Tous' },
  { valeur: 'present', label: 'Présent' },
  { valeur: 'absent', label: 'Absent' },
];

export default function PresencesEnseignantPage() {
  const aujourd_hui = new Date().toISOString().slice(0, 10);
  const debutDuMois = aujourd_hui.slice(0, 8) + '01';

  const [dateDebut, setDateDebut] = useState(debutDuMois);
  const [dateFin, setDateFin] = useState(aujourd_hui);
  const [statut, setStatut] = useState('');
  const [groupeId, setGroupeId] = useState('');
  const [coursId, setCoursId] = useState('');
  const [etudiant, setEtudiant] = useState('');

  const [groupes, setGroupes] = useState([]);
  const [cours, setCours] = useState([]);

  const [presences, setPresences] = useState([]);
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState(null);
  const [rechercheFaite, setRechercheFaite] = useState(false);

  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');

  const headers = {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
  };

  useEffect(() => {
    const fetchFiltres = async () => {
      try {
        const [resGroupes, resSeances] = await Promise.all([
          fetch(`${import.meta.env.VITE_API_URL}/groupes`, { headers }),
          fetch(`${import.meta.env.VITE_API_URL}/seances?enseignant_id=${user.id}&par_page=50`, {
            headers,
          }),
        ]);

        const [dataGroupes, dataSeances] = await Promise.all([
          resGroupes.json(),
          resSeances.json(),
        ]);

        setGroupes(Array.isArray(dataGroupes) ? dataGroupes : []);

        const seances = dataSeances.data ?? [];

        const coursEnseignant = seances
          .map((s) => s.cours)
          .filter(Boolean)
          .filter((cours, index, self) => index === self.findIndex((c) => c.id === cours.id));

        setCours(coursEnseignant);
      } catch {
        setGroupes([]);
        setCours([]);
      }
    };

    if (user.id) {
      fetchFiltres();
    }
  }, [user.id]);

  const construireParams = (extras = {}) => {
    const params = new URLSearchParams({
      date_debut: dateDebut,
      date_fin: dateFin,
    });

    if (statut) params.set('statut', statut);
    if (groupeId) params.set('groupe_id', groupeId);
    if (coursId) params.set('cours_id', coursId);
    if (etudiant.trim()) params.set('etudiant', etudiant.trim());

    params.set('enseignant_id', user.id);

    Object.entries(extras).forEach(([k, v]) => params.set(k, v));

    return params;
  };

  const rechercher = async (e) => {
    e.preventDefault();
    setChargement(true);
    setErreur(null);
    setRechercheFaite(true);

    try {
      const res = await fetch(
        `${import.meta.env.VITE_API_URL}/getStatutAndByDate?${construireParams()}`,
        { headers }
      );

      const data = await res.json();

      if (res.ok && data.success) {
        const coursNomsAutorises = cours.map((c) => c.nom);

        const resultatsFiltres = (data.data ?? []).filter((p) =>
          coursNomsAutorises.includes(p.cours_nom)
        );

        setPresences(resultatsFiltres);
      } else {
        setPresences([]);
        setErreur(data.message ?? null);
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setChargement(false);
    }
  };

  const formatDate = (dateStr) => {
    if (!dateStr) return '—';

    return new Date(dateStr).toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div className="presences-page">
      <h1 className="page-titre">Archives de mes cours</h1>

      <section className="section-filtres">
        <form onSubmit={rechercher} className="form-filtres">
          <div className="filtre-groupe">
            <label htmlFor="date-debut">Du</label>
            <input
              id="date-debut"
              type="date"
              value={dateDebut}
              onChange={(e) => setDateDebut(e.target.value)}
              className="input-date"
            />
          </div>

          <div className="filtre-groupe">
            <label htmlFor="date-fin">Au</label>
            <input
              id="date-fin"
              type="date"
              value={dateFin}
              onChange={(e) => setDateFin(e.target.value)}
              min={dateDebut}
              className="input-date"
            />
          </div>

          <div className="filtre-groupe">
            <label htmlFor="statut">Statut</label>
            <select
              id="statut"
              value={statut}
              onChange={(e) => setStatut(e.target.value)}
              className="select-statut"
            >
              {STATUTS.map((s) => (
                <option key={s.valeur} value={s.valeur}>
                  {s.label}
                </option>
              ))}
            </select>
          </div>

          <div className="filtre-groupe">
            <label htmlFor="groupe">Groupe</label>
            <select
              id="groupe"
              value={groupeId}
              onChange={(e) => setGroupeId(e.target.value)}
              className="select-statut"
            >
              <option value="">Tous</option>
              {groupes.map((g) => (
                <option key={g.id} value={g.id}>
                  {g.nom}
                </option>
              ))}
            </select>
          </div>

          <div className="filtre-groupe">
            <label htmlFor="cours">Cours</label>
            <select
              id="cours"
              value={coursId}
              onChange={(e) => setCoursId(e.target.value)}
              className="select-statut"
            >
              <option value="">Tous mes cours</option>
              {cours.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.nom}
                </option>
              ))}
            </select>
          </div>

          <div className="filtre-groupe">
            <label htmlFor="etudiant">Étudiant</label>
            <input
              id="etudiant"
              type="text"
              value={etudiant}
              onChange={(e) => setEtudiant(e.target.value)}
              placeholder="Nom ou prénom…"
              className="input-texte"
            />
          </div>

          <button type="submit" className="btn-rechercher" disabled={chargement}>
            {chargement ? 'Chargement...' : 'Rechercher'}
          </button>
        </form>
      </section>

      {erreur && <p className="message-erreur">{erreur}</p>}

      {rechercheFaite && !chargement && (
        <section className="section-resultats">
          {presences.length === 0 ? (
            <p className="message-info">Aucun résultat pour ces critères.</p>
          ) : (
            <>
              <p className="compteur">{presences.length} résultat(s)</p>

              <table className="tableau-presences">
                <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Groupe</th>
                    <th>Cours</th>
                    <th>Date / Heure</th>
                  </tr>
                </thead>

                <tbody>
                  {presences.map((p, index) => (
                    <tr key={index}>
                      <td data-label="Nom">{p.nom}</td>
                      <td data-label="Prénom">{p.prenom}</td>
                      <td data-label="Email">{p.email}</td>
                      <td data-label="Statut">
                        <span className={`badge-statut badge-${p.statut}`}>
                          {p.statut === 'present' ? 'Présent' : 'Absent'}
                        </span>
                      </td>
                      <td data-label="Groupe">{p.groupe_nom ?? '—'}</td>
                      <td data-label="Cours">{p.cours_nom}</td>
                      <td data-label="Date / Heure">{formatDate(p.presence_date)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </>
          )}
        </section>
      )}
    </div>
  );
}
