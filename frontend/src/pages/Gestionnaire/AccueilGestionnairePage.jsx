import React, { useState, useEffect } from 'react';
import './AccueilGestionnairePage.css';

function statutInvitation(inv) {
  if (inv.used_at) return { label: 'Utilisée', classe: 'statut-utilisee' };
  if (new Date(inv.expires_at) < new Date()) return { label: 'Expirée', classe: 'statut-expiree' };
  return { label: 'En attente', classe: 'statut-attente' };
}

export default function AccueilGestionnairePage() {
  const [invitations, setInvitations] = useState([]);
  const [demandes, setDemandes] = useState([]);
  const [email, setEmail] = useState('');
  const [chargement, setChargement] = useState(true);
  const [envoi, setEnvoi] = useState(false);
  const [erreur, setErreur] = useState(null);
  const [succes, setSucces] = useState(null);

  const token = localStorage.getItem('token');
  const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' };

  const chargerInvitations = async () => {
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/invitations`, {
        headers,
      });
      const data = await res.json();
      if (res.ok) setInvitations(data.data ?? data);
    } catch {
      setErreur('Impossible de charger les invitations.');
    } finally {
      setChargement(false);
    }
  };

  const chargerDemandes = async () => {
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/demandes`, {
        headers,
      });
      const data = await res.json();
      if (res.ok) setDemandes(data.data ?? data);
    } catch {
      // silencieux
    }
  };

  useEffect(() => {
    chargerInvitations();
    chargerDemandes();
  }, []);

  const inviter = async (e) => {
    e.preventDefault();
    setEnvoi(true);
    setErreur(null);
    setSucces(null);
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/invitations`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();
      if (res.ok) {
        setSucces(`Invitation envoyée à ${email}.`);
        setEmail('');
        chargerInvitations();
      } else {
        const messages = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        setErreur(messages || "Erreur lors de l'envoi.");
      }
    } catch {
      setErreur('Impossible de contacter le serveur.');
    } finally {
      setEnvoi(false);
    }
  };

  const annuler = async (id) => {
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/invitations/${id}`, {
        method: 'DELETE',
        headers,
      });
      if (res.ok) chargerInvitations();
    } catch {
      setErreur("Erreur lors de l'annulation.");
    }
  };

  const approuverDemande = async (id) => {
    try {
      const res = await fetch(
        `${import.meta.env.VITE_API_URL}/gestionnaire/demandes/${id}/approuver`,
        { method: 'POST', headers }
      );
      if (res.ok) {
        setSucces('Invitation envoyée.');
        chargerDemandes();
        chargerInvitations();
      }
    } catch {
      setErreur("Erreur lors de l'approbation.");
    }
  };

  const refuserDemande = async (id) => {
    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/demandes/${id}`, {
        method: 'DELETE',
        headers,
      });
      if (res.ok) chargerDemandes();
    } catch {
      setErreur('Erreur lors du refus.');
    }
  };

  const renvoyer = async (id) => {
    try {
      const res = await fetch(
        `${import.meta.env.VITE_API_URL}/gestionnaire/invitations/${id}/renvoyer`,
        {
          method: 'POST',
          headers,
        }
      );
      if (res.ok) {
        setSucces('Invitation renvoyée.');
        chargerInvitations();
      }
    } catch {
      setErreur('Erreur lors du renvoi.');
    }
  };

  return (
    <div className="gestionnaire-page">
      <h1 className="page-titre">Gestion des gestionnaires</h1>

      <section className="section-inviter">
        <h2>Inviter un gestionnaire</h2>
        <form onSubmit={inviter} className="form-invitation">
          <input
            type="email"
            placeholder="adresse@email.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            className="input-email"
          />
          <button type="submit" disabled={envoi} className="btn-inviter">
            {envoi ? 'Envoi...' : "Envoyer l'invitation"}
          </button>
        </form>
        {succes && <p className="message-succes">{succes}</p>}
        {erreur && <p className="message-erreur">{erreur}</p>}
      </section>

      {demandes.length > 0 && (
        <section className="section-liste">
          <h2>Demandes reçues</h2>
          <div className="liste-invitations">
            {demandes.map((demande) => (
              <div key={demande.id} className="carte-invitation">
                <div className="carte-info">
                  <span className="carte-email">{demande.email}</span>
                  <span className="statut-badge statut-attente">En attente</span>
                </div>
                <div className="carte-actions">
                  <button
                    onClick={() => approuverDemande(demande.id)}
                    className="btn-action btn-renvoyer"
                  >
                    Approuver
                  </button>
                  <button
                    onClick={() => refuserDemande(demande.id)}
                    className="btn-action btn-annuler"
                  >
                    Refuser
                  </button>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      <section className="section-liste">
        <h2>Invitations envoyées</h2>
        {chargement && <p className="message-info">Chargement...</p>}
        {!chargement && invitations.length === 0 && (
          <p className="message-info">Aucune invitation pour l'instant.</p>
        )}
        {invitations.length > 0 && (
          <div className="liste-invitations">
            {invitations.map((inv) => {
              const statut = statutInvitation(inv);
              const estUtilisee = !!inv.used_at;
              return (
                <div key={inv.id} className="carte-invitation">
                  <div className="carte-info">
                    <span className="carte-email">{inv.email}</span>
                    <span className={`statut-badge ${statut.classe}`}>{statut.label}</span>
                  </div>
                  {!estUtilisee && (
                    <div className="carte-actions">
                      <button onClick={() => renvoyer(inv.id)} className="btn-action btn-renvoyer">
                        Renvoyer
                      </button>
                      <button onClick={() => annuler(inv.id)} className="btn-action btn-annuler">
                        Annuler
                      </button>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </section>
    </div>
  );
}
