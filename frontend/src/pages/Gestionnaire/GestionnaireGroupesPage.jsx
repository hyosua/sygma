import { useState, useEffect, useCallback } from 'react';
import './GestionnaireGroupesPage.css';

const token = localStorage.getItem('token');
const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' };

export default function GestionnaireGroupesPage() {
  const [groupes, setGroupes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [nom, setNom] = useState('');
  const [promotion, setPromotion] = useState('');
  const [envoi, setEnvoi] = useState(false);
  const [erreur, setErreur] = useState(null);
  const [succes, setSucces] = useState(null);
  const [edition, setEdition] = useState(null); // { id, nom, promotion }

  const chargerGroupes = useCallback(async () => {
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/groupes`, { headers });
      const data = await res.json();
      if (res.ok) setGroupes(data.data ?? data);
    } catch {
      setErreur('Impossible de charger les groupes.');
    } finally {
      setChargement(false);
    }
  }, []);

  useEffect(() => {
    chargerGroupes();
  }, [chargerGroupes]);

  const creer = async (e) => {
    e.preventDefault();
    setEnvoi(true);
    setErreur(null);
    setSucces(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/groupes`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, promotion }),
      });
      const data = await res.json();
      if (res.ok) {
        setSucces(`Groupe "${nom}" créé.`);
        setNom('');
        setPromotion('');
        chargerGroupes();
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreur(messages || 'Erreur lors de la création.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setEnvoi(false);
    }
  };

  const sauvegarderEdition = async (id) => {
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/groupes/${id}`, {
        method: 'PATCH',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom: edition.nom, promotion: edition.promotion }),
      });
      const data = await res.json();
      if (res.ok) {
        setEdition(null);
        chargerGroupes();
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreur(messages || 'Erreur lors de la modification.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    }
  };

  const supprimer = async (id, nomGroupe) => {
    if (!window.confirm(`Supprimer le groupe "${nomGroupe}" ?`)) return;
    setErreur(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/groupes/${id}`, {
        method: 'DELETE',
        headers,
      });
      if (res.ok) {
        chargerGroupes();
      } else {
        setErreur('Erreur lors de la suppression.');
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    }
  };

  return (
    <div className="groupes-page">
      <h1 className="page-titre">Gestion des groupes</h1>

      <section className="section-groupes">
        <h2>Créer un groupe</h2>
        <form onSubmit={creer} className="form-groupe">
          <input
            type="text"
            placeholder="Nom du groupe (ex: LP Dawii)"
            value={nom}
            onChange={(e) => setNom(e.target.value)}
            required
            className="input-groupe"
          />
          <input
            type="text"
            placeholder="Promotion (ex: 2025-2026)"
            value={promotion}
            onChange={(e) => setPromotion(e.target.value)}
            className="input-groupe"
          />
          <button type="submit" disabled={envoi} className="btn-creer">
            {envoi ? 'Création...' : 'Créer'}
          </button>
        </form>
        {succes && <p className="message-succes">{succes}</p>}
        {erreur && <p className="message-erreur">{erreur}</p>}
      </section>

      <section className="section-groupes">
        <h2>Groupes existants</h2>
        {chargement && <p className="message-info">Chargement...</p>}
        {!chargement && groupes.length === 0 && (
          <p className="message-info">Aucun groupe pour l'instant.</p>
        )}
        {groupes.length > 0 && (
          <div className="liste-groupes">
            {groupes.map((g) => (
              <div key={g.id} className="carte-groupe">
                {edition?.id === g.id ? (
                  <div className="carte-edition">
                    <input
                      type="text"
                      value={edition.nom}
                      onChange={(e) => setEdition({ ...edition, nom: e.target.value })}
                      className="input-groupe"
                    />
                    <input
                      type="text"
                      value={edition.promotion ?? ''}
                      onChange={(e) => setEdition({ ...edition, promotion: e.target.value })}
                      placeholder="Promotion"
                      className="input-groupe"
                    />
                    <div className="carte-actions">
                      <button
                        onClick={() => sauvegarderEdition(g.id)}
                        className="btn-action btn-sauvegarder"
                      >
                        Sauvegarder
                      </button>
                      <button onClick={() => setEdition(null)} className="btn-action btn-annuler">
                        Annuler
                      </button>
                    </div>
                  </div>
                ) : (
                  <>
                    <div className="carte-info">
                      <span className="carte-nom">{g.nom}</span>
                      {g.promotion && <span className="carte-promotion">{g.promotion}</span>}
                    </div>
                    <div className="carte-actions">
                      <button
                        onClick={() => setEdition({ id: g.id, nom: g.nom, promotion: g.promotion })}
                        className="btn-action btn-modifier"
                      >
                        Modifier
                      </button>
                      <button
                        onClick={() => supprimer(g.id, g.nom)}
                        className="btn-action btn-supprimer"
                      >
                        Supprimer
                      </button>
                    </div>
                  </>
                )}
              </div>
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
