import { useEffect, useState } from 'react';
import './MesPresences.css';
import { useLocation } from 'react-router-dom';
function MesPresences() {
  const location = useLocation();
  const [statuts, setStatuts] = useState(location.state?.onglet ?? false); // true = présent, false = absent
  const [data, setData] = useState([]);

  const API_BASE = import.meta.env.VITE_API_URL;
  function authHeaders() {
    const token = localStorage.getItem('token');
    return {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
    };
  }

  async function getData() {
    try {
      const stat = statuts === false ? 'a' : 'p';
      const user = JSON.parse(localStorage.getItem('user'));

      const api = await fetch(`${API_BASE}/mes-presences/${user.id}?statuts=${stat}`, {
        headers: authHeaders(),
      });

      if (!api.ok) {
        console.error('Erreur API:', api.status);
        return;
      }

      const json = await api.json();
      console.log(json);

      const donneesTriees = (json.data ?? []).sort((a, b) => new Date(b.date) - new Date(a.date));

      setData(donneesTriees);
    } catch (error) {
      console.error('Erreur:', error);
    }
  }

  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    getData();
    console.log(data);
  }, [statuts]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <div className="mes-presences-page">
      <div className="presences-content">
        <div className="hero-card">
          <h1>Mes présences</h1>
          <p>Consulte tes absences et présences par cours et par mois.</p>
          <div className="tabs">
            <button
              className={`tab ${statuts == false ? '' : 'active'}`}
              onClick={() => setStatuts(true)}
            >
              Présent
            </button>

            <button
              className={`tab ${statuts == true ? false : 'active'}`}
              onClick={() => setStatuts(false)}
            >
              Absent
            </button>
          </div>
        </div>

        <div className="presences-panel">
          <table className="presences-table">
            <thead>
              <tr>
                <th style={{ textAlign: 'center' }}>Cours</th>
                <th style={{ textAlign: 'center' }}>Statut</th>
                <th style={{ textAlign: 'center' }}>Date</th>
              </tr>
            </thead>

            <tbody>
              {data.map((value, index) => (
                <tr key={index}>
                  <td>{value.nom}</td>

                  <td>
                    <span className={`badge ${value.statut}`}>{value.statut}</span>
                  </td>

                  <td>{value.date}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

export default MesPresences;
