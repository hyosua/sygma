import React, { useState } from 'react';
import './PresencesGestionnairePage.css';

const STATUTS = [
  { valeur: 'present', label: 'Présent' },
  { valeur: 'absent', label: 'Absent' },
];

export default function PresencesGestionnairePage() {
  // Filtres sélectionnés par l'utilisateur
  const [date, setDate] = useState('');
  const [statut, setStatut] = useState('present');

  // État de la liste affichée
  const [presences, setPresences] = useState([]);
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState(null);
  const [rechercheFaite, setRechercheFaite] = useState(false);

  const token = localStorage.getItem('token');
  const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' };

  // Recherche JSON → affiche le tableau
  const rechercher = async (e) => {
    e.preventDefault();
    setChargement(true);
    setErreur(null);
    setRechercheFaite(true);

    try {
      const params = new URLSearchParams({ date, statut });
      const res = await fetch(`${import.meta.env.VITE_API_URL}/getStatutAndByDate?${params}`, {
        headers,
      });
      const data = await res.json();
      if (res.ok && data.success) {
        setPresences(data.data);
      } else {
        setPresences([]);
        if (data.success === false)
          setErreur(null); // Pas d'erreur, juste aucun résultat
        else setErreur(data.message ?? 'Erreur lors de la recherche.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setChargement(false);
    }
  };

  // Télécharge un fichier (Excel ou PDF) via fetch + blob.
  // On utilise fetch plutôt que window.open car la route est protégée par un token Bearer
  // que le navigateur ne peut pas envoyer automatiquement via une simple URL.
  const telecharger = async (type) => {
    try {
      const params = new URLSearchParams({ date, statut, type });
      const res = await fetch(`${import.meta.env.VITE_API_URL}/getStatutAndByDate?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (!res.ok) {
        setErreur("Erreur lors de l'export.");
        return;
      }

      // On récupère le contenu binaire (fichier xlsx ou pdf)
      const blob = await res.blob();

      // On crée un lien invisible qui pointe vers ce blob, puis on le "clique"
      const lienTemp = document.createElement('a');
      lienTemp.href = URL.createObjectURL(blob);
      lienTemp.download = `presences_${statut}_${date}.${type === 'E' ? 'xlsx' : 'pdf'}`;
      lienTemp.click();

      // Nettoyage : libère la mémoire allouée au blob
      URL.revokeObjectURL(lienTemp.href);
    } catch {
      setErreur('Erreur lors du téléchargement.');
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
      <h1 className="page-titre">Présences</h1>

      {/* Formulaire de filtres */}
      <section className="section-filtres">
        <form onSubmit={rechercher} className="form-filtres">
          <div className="filtre-groupe">
            <label htmlFor="date">Date</label>
            <input
              id="date"
              type="date"
              value={date}
              onChange={(e) => setDate(e.target.value)}
              required
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

          <button type="submit" className="btn-rechercher" disabled={chargement}>
            {chargement ? 'Chargement...' : 'Rechercher'}
          </button>
        </form>

        {/* Boutons d'export — disponibles seulement après une recherche avec résultats */}
        {presences.length > 0 && (
          <div className="export-actions">
            <button onClick={() => telecharger('E')} className="btn-export btn-excel">
              Exporter Excel
            </button>
            <button onClick={() => telecharger('P')} className="btn-export btn-pdf">
              Exporter PDF
            </button>
          </div>
        )}
      </section>

      {erreur && <p className="message-erreur">{erreur}</p>}

      {/* Tableau des résultats */}
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
                    <th>Groupe</th>
                    <th>Cours</th>
                    <th>Date / Heure</th>
                  </tr>
                </thead>
                <tbody>
                  {presences.map((p, index) => (
                    <tr key={index}>
                      <td>{p.nom}</td>
                      <td>{p.prenom}</td>
                      <td>{p.email}</td>
                      <td>{p.groupe_nom ?? '—'}</td>
                      <td>{p.cours_nom}</td>
                      <td>{formatDate(p.presence_date)}</td>
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
