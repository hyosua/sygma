import React, { useMemo, useState } from 'react';
import './MesCoursEtudiantPage.css';
import { useNavigate } from 'react-router-dom';

const mockCourses = [
  {
    id: 1,
    nom: 'Développement Web',
    salle: 'B204',
    classe: 'LP MIAW',
    professeur: 'Mme Dupont',
    date: '22 mars 2026',
    heureDebut: '09:00',
    heureFin: '11:00',
    statut: 'en-cours',
  },
  {
    id: 2,
    nom: 'Base de données',
    salle: 'C110',
    classe: 'BUT INFO 2',
    professeur: 'M. Bernard',
    date: '22 mars 2026',
    heureDebut: '14:00',
    heureFin: '16:00',
    statut: 'a-venir',
  },
  {
    id: 3,
    nom: 'Programmation React',
    salle: 'A301',
    classe: 'LP MIAW',
    professeur: 'Mme Martin',
    date: '22 mars 2026',
    heureDebut: '16:30',
    heureFin: '18:00',
    statut: 'a-venir',
  },
  {
    id: 4,
    nom: 'Architecture logicielle',
    salle: 'D205',
    classe: 'Master 1',
    professeur: 'M. Leroy',
    date: '22 mars 2026',
    heureDebut: '10:00',
    heureFin: '12:00',
    statut: 'en-cours',
  },
];

function CourseCard({ course, onEmarger }) {
  return (
    <div className="course-card">
      <div className="course-top">
        <div>
          <p className="course-badge">{course.statut === 'en-cours' ? 'En cours' : 'À venir'}</p>
          <h3 className="course-title">{course.nom}</h3>
        </div>
      </div>

      <div className="course-infos">
        <div className="info-box">
          <span className="info-label">Salle</span>
          <span className="info-value">{course.salle}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Classe</span>
          <span className="info-value">{course.classe}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Professeur</span>
          <span className="info-value">{course.professeur}</span>
        </div>

        <div className="info-box">
          <span className="info-label">Horaire prévu</span>
          <span className="info-value">
            {course.date} · {course.heureDebut} - {course.heureFin}
          </span>
        </div>
      </div>

      {course.statut === 'en-cours' && (
        <div className="course-actions">
          <button className="emarger-button" onClick={() => onEmarger(course)}>
            Émarger
          </button>
        </div>
      )}
    </div>
  );
}

export default function MesCoursEtudiantPage() {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('en-cours');

  const handleEmarger = (course) => {
    navigate(`/etudiant/scan/${course.id}`);
  };

  const filteredCourses = useMemo(() => {
    return mockCourses.filter((course) => course.statut === activeTab);
  }, [activeTab]);

  return (
    <div className="mes-cours-page">
      <div className="overlay" />

      <div className="content">
        <header className="hero">
          <div>
            <p className="hero-tag">Espace étudiant</p>
            <h1>Mes cours</h1>
            <p className="hero-subtitle">
              Consultez vos cours en cours et à venir, avec les informations de salle, classe,
              professeur et horaire.
            </p>
          </div>
        </header>

        <section className="panel">
          <div className="tabs">
            <button
              className={`tab ${activeTab === 'en-cours' ? 'active' : ''}`}
              onClick={() => setActiveTab('en-cours')}
            >
              Cours en cours
            </button>

            <button
              className={`tab ${activeTab === 'a-venir' ? 'active' : ''}`}
              onClick={() => setActiveTab('a-venir')}
            >
              Cours à venir
            </button>
          </div>

          <div className="courses-list">
            {filteredCourses.length > 0 ? (
              filteredCourses.map((course) => (
                <CourseCard key={course.id} course={course} onEmarger={handleEmarger} />
              ))
            ) : (
              <div className="empty-state">
                <h3>Aucun cours disponible</h3>
                <p>Il n’y a aucun cours dans cet onglet pour le moment.</p>
              </div>
            )}
          </div>
        </section>
      </div>
    </div>
  );
}
