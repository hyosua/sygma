import { useEffect, useState } from 'react';
import './ProfilEnseignantPage.css';

const API_BASE = import.meta.env.VITE_API_URL;

export default function ProfilEnseignantPage() {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  const token = localStorage.getItem('token');

  const defaultAvatar =
    'https://res.cloudinary.com/dvrh8p8m0/image/upload/w_500,h_500,c_fill,g_auto,q_auto,f_auto/pp-anonyme_qott5m';

  const [photoPreview, setPhotoPreview] = useState(user.url_image_profil || defaultAvatar);
  const [cours, setCours] = useState([]);

  useEffect(() => {
    const fetchCours = async () => {
      try {
        const res = await fetch(`${API_BASE}/seances?enseignant_id=${user.id}&par_page=50`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        });

        const data = await res.json();

        const coursUniques = (data.data ?? [])
          .map((s) => s.cours)
          .filter(Boolean)
          .filter((cours, index, self) => index === self.findIndex((c) => c.id === cours.id));

        setCours(coursUniques);
      } catch (err) {
        console.error('Erreur chargement cours enseignant', err);
      }
    };

    if (user.id) {
      fetchCours();
    }
  }, [user.id, token]);

  const handlePhotoChange = (e) => {
    const file = e.target.files[0];

    if (!file) return;

    const previewUrl = URL.createObjectURL(file);
    setPhotoPreview(previewUrl);

    // Plus tard : envoyer l’image au backend avec FormData
  };

  return (
    <div className="profil-page">
      <section className="hero-card">
        <div className="profil-avatar-wrapper">
          <img
            src={photoPreview}
            alt="Photo de profil"
            className="profil-avatar"
            onError={(e) => {
              e.target.src = defaultAvatar;
            }}
          />
          <label className="change-photo-btn">
            Changer la photo
            <input type="file" accept="image/*" onChange={handlePhotoChange} hidden />
          </label>
        </div>

        <div className="profil-info">
          <h1>
            {user.prenom || 'Prénom'} {user.nom || 'Nom'}
          </h1>
          <p className="hero-text">{user.email || 'email non renseigné'}</p>
        </div>
      </section>

      <section className="profil-section">
        <h2>Cours enseignés</h2>

        {cours.length > 0 ? (
          <div className="cours-grid">
            {cours.map((c) => (
              <div key={c.id} className="cours-card">
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
                </svg>
                <h3>{c.nom}</h3>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-profile-state">
            <p>Aucun cours associé pour le moment.</p>
          </div>
        )}
      </section>
    </div>
  );
}
