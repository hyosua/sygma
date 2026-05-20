import { useState, useRef } from 'react';
import './ImportComptesPage.css';

export default function ImportComptesPage() {
  const [fichier, setFichier] = useState(null);
  const [envoi, setEnvoi] = useState(false);
  const [message, setMessage] = useState(null);
  const [erreur, setErreur] = useState(null);
  const [survol, setSurvol] = useState(false);
  const inputRef = useRef(null);

  const token = localStorage.getItem('token');
  const headers = {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
  };

  const importerComptes = async (e) => {
    e.preventDefault();
    setEnvoi(true);
    setMessage(null);
    setErreur(null);

    const formData = new FormData();
    formData.append('fichier', fichier);

    try {
      const res = await fetch(`${import.meta.env.VITE_API_URL}/gestionnaire/comptes/import`, {
        method: 'POST',
        headers,
        body: formData,
      });

      const data = await res.json();
      if (res.ok) {
        setMessage(data);
      } else {
        setErreur(data || "Une erreur est survenue lors de l'importation.");
      }
    } catch {
      setErreur('Une erreur réseau est survenue. Veuillez réessayer.');
    } finally {
      setEnvoi(false);
    }
  };

  return (
    <div className="import-page">
      <h1 className="page-titre">Importation de comptes</h1>

      <section className="section-import">
        <h2>Importer un fichier CSV ou Excel</h2>
        <p className="import-hint">
          Colonnes attendues : <strong>nom, prenom, email, role</strong> (ex: enseignant ou
          etudiant) et optionnellement <strong>groupe</strong> pour affecter un étudiant à un groupe
          (créé automatiquement si inexistant).
          <br />
        </p>
        <form onSubmit={importerComptes} className="form-import">
          <button
            type="button"
            className={`zone-depot${survol ? ' zone-depot--survol' : ''}`}
            onClick={() => inputRef.current.click()}
            onKeyDown={(e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                inputRef.current.click();
              }
            }}
            onDragOver={(e) => {
              e.preventDefault();
              setSurvol(true);
            }}
            onDragLeave={() => setSurvol(false)}
            onDrop={(e) => {
              e.preventDefault();
              setSurvol(false);
              const f = e.dataTransfer.files[0];
              if (f) setFichier(f);
              setErreur(null);
            }}
          >
            <input
              ref={inputRef}
              type="file"
              accept=".csv,.xlsx"
              onChange={(e) => {
                setFichier(e.target.files[0]);
                setErreur(null);
              }}
              className="input-fichier-cache"
            />
            {fichier ? (
              <span className="zone-depot-nom">{fichier.name}</span>
            ) : (
              <span className="zone-depot-texte">
                Glissez un fichier ici ou <u>parcourir</u>
              </span>
            )}
          </button>
          <button type="submit" disabled={envoi || !fichier} className="btn-importer">
            {envoi ? 'Importation en cours...' : 'Importer'}
          </button>
        </form>
        {erreur && <p className="message-erreur">{erreur}</p>}
      </section>

      {message && (
        <section className="section-import">
          <h2>Résultat de l'importation</h2>
          {message.success > 0 && (
            <>
              <p className="rapport-succes">
                {message.success} compte{message.success > 1 ? 's' : ''} créé
                {message.success > 1 ? 's' : ''} :
              </p>
              <table className="rapport-table">
                <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Groupe</th>
                  </tr>
                </thead>
                <tbody>
                  {message.comptes?.map((c, i) => (
                    <tr key={i}>
                      <td>{c.nom}</td>
                      <td>{c.prenom}</td>
                      <td>{c.email}</td>
                      <td>{c.role}</td>
                      <td>{c.groupe ?? '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </>
          )}
          {message.erreurs.length > 0 && (
            <>
              <p className="rapport-erreurs-titre">Erreurs :</p>
              <ul className="rapport-erreurs-liste">
                {message.erreurs.map((err, index) => (
                  <li key={index} className="rapport-erreur-item">
                    {err}
                  </li>
                ))}
              </ul>
            </>
          )}
        </section>
      )}
    </div>
  );
}
