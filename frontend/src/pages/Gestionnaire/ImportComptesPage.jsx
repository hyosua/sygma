import { useState } from 'react';

export default function ImportComptesPage() {
  const [fichier, setFichier] = useState(null);
  const [envoi, setEnvoi] = useState(false);
  const [message, setMessage] = useState(null);
  const [erreur, setErreur] = useState(null);

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
      const res = await fetch(`${import.meta.env.VITE_API_URL}/importer-comptes`, {
        method: 'POST',
        headers,
        body: formData,
      });

      const data = await res.json();
      console.log(data);
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
          Colonnes attendues : <strong>email, nom, prenom, role</strong> (ex: enseignant ou
          etudiant).
          <br />
        </p>
        <form onSubmit={importerComptes} className="form-import">
          <input
            type="file"
            accept=".csv,.xlsx"
            onChange={(e) => setFichier(e.target.files[0])}
            required
            className="input-fichier"
          />
          <button type="submit" disabled={envoi || !fichier} className="btn-importer">
            {envoi ? 'Importation en cours...' : 'Importer'}
          </button>
        </form>
        {erreur && <p className="message-erreur">{erreur}</p>}
      </section>

      {message && (
        <section className="section-import">
          <h2>Résultat de l'importation</h2>
          <p className="rapport-success">
            {message.success} compte{message.success > 1 ? 's' : ''} créé
            {message.success > 1 ? 's' : ''} :
          </p>
          <ul className="rapport-erreurs-liste">
            {message.erreurs.map((err, index) => (
              <li key={index} className="rapport-erreur-item">
                {err}
              </li>
            ))}
          </ul>
        </section>
      )}
    </div>
  );
}
