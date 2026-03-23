import React, { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./MesCoursPage.css";

const mockCourses = [
  {
    id: 1,
    nom: "Développement Web",
    salle: "B204",
    classe: "LP MIAW",
    date: "22 mars 2026",
    heureDebut: "09:00",
    heureFin: "11:00",
    statut: "en-cours",
  },
  {
    id: 2,
    nom: "Base de données",
    salle: "C110",
    classe: "BUT INFO 2",
    date: "22 mars 2026",
    heureDebut: "14:00",
    heureFin: "16:00",
    statut: "a-venir",
  },
  {
    id: 3,
    nom: "Programmation React",
    salle: "A301",
    classe: "LP MIAW",
    date: "22 mars 2026",
    heureDebut: "16:30",
    heureFin: "18:00",
    statut: "a-venir",
  },
  {
    id: 4,
    nom: "Architecture logicielle",
    salle: "D205",
    classe: "Master 1",
    date: "22 mars 2026",
    heureDebut: "10:00",
    heureFin: "12:00",
    statut: "en-cours",
  },
];

function CourseCard({ course, onStart }) {
  return (
    <div className="course-card">
      <div className="course-top">
        <div>
          <p className="course-badge">
            {course.statut === "en-cours" ? "En cours" : "À venir"}
          </p>
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
          <span className="info-label">Horaire prévu</span>
          <span className="info-value">
            {course.date} · {course.heureDebut} - {course.heureFin}
          </span>
        </div>
      </div>

      <div className="course-actions">
        <button className="start-button" onClick={() => onStart(course)}>
          Démarrer l’émargement
        </button>
      </div>
    </div>
  );
}

export default function MesCoursPage() {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState("en-cours");
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [courseMode, setCourseMode] = useState("existing");

  const [formData, setFormData] = useState({
    selectedCourseId: "",
    nom: "",
    salle: "",
    classe: "",
    date: "",
    heureDebut: "",
    heureFin: "",
  });

  const filteredCourses = useMemo(() => {
    return mockCourses.filter((course) => course.statut === activeTab);
  }, [activeTab]);

  const handleStartAttendance = (course) => {
    navigate(`/enseignant/session/${course.id}`);
  };

  const handleOpenModal = () => {
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmitSession = (e) => {
    e.preventDefault();

    if (courseMode === "existing") {
      console.log("Créer séance avec cours existant :", {
        courseId: formData.selectedCourseId,
        date: formData.date,
        heureDebut: formData.heureDebut,
        heureFin: formData.heureFin,
      });
    } else {
      console.log("Créer séance avec nouveau cours :", formData);
    }

    setIsModalOpen(false);

    setFormData({
      selectedCourseId: "",
      nom: "",
      salle: "",
      classe: "",
      date: "",
      heureDebut: "",
      heureFin: "",
    });

    setCourseMode("existing");
  };

  return (
    <div className="mes-cours-page">
      <div className="overlay" />

      <div className="content">
        <header className="hero">
          <div>
            <p className="hero-tag">Espace enseignant</p>
            <h1>Mes cours</h1>
            <p className="hero-subtitle">
              Retrouvez vos cours en cours et à venir, puis lancez rapidement
              l’émargement de votre séance.
            </p>
          </div>
        </header>

        <section className="panel">
          <div className="tabs">
            <button
              className={`tab ${activeTab === "en-cours" ? "active" : ""}`}
              onClick={() => setActiveTab("en-cours")}
            >
              Cours en cours
            </button>

            <button
              className={`tab ${activeTab === "a-venir" ? "active" : ""}`}
              onClick={() => setActiveTab("a-venir")}
            >
              Cours à venir
            </button>
          </div>

          <div className="courses-list">
            {filteredCourses.length > 0 ? (
              filteredCourses.map((course) => (
                <CourseCard
                  key={course.id}
                  course={course}
                  onStart={handleStartAttendance}
                />
              ))
            ) : (
              <div className="empty-state">
                <h3>Aucun cours disponible</h3>
                <p>Il n’y a aucun cours dans cet onglet pour le moment.</p>
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
                <div className="mode-switch">
                  <button
                    type="button"
                    className={`mode-btn ${courseMode === "existing" ? "active" : ""}`}
                    onClick={() => setCourseMode("existing")}
                  >
                    Cours existant
                  </button>
                  <button
                    type="button"
                    className={`mode-btn ${courseMode === "new" ? "active" : ""}`}
                    onClick={() => setCourseMode("new")}
                  >
                    Nouveau cours
                  </button>
                </div>

                {courseMode === "existing" ? (
                  <div className="form-group">
                    <label>Choisir un cours</label>
                    <select
                      name="selectedCourseId"
                      value={formData.selectedCourseId}
                      onChange={handleChange}
                      required
                    >
                      <option value="">Sélectionnez un cours</option>
                      {mockCourses.map((course) => (
                        <option key={course.id} value={course.id}>
                          {course.nom} — {course.classe} — Salle {course.salle}
                        </option>
                      ))}
                    </select>
                  </div>
                ) : (
                  <>
                    <div className="form-group">
                      <label>Nom du cours</label>
                      <input
                        type="text"
                        name="nom"
                        value={formData.nom}
                        onChange={handleChange}
                        placeholder="Ex : Développement Web"
                        required
                      />
                    </div>

                    <div className="form-row">
                      <div className="form-group">
                        <label>Salle</label>
                        <input
                          type="text"
                          name="salle"
                          value={formData.salle}
                          onChange={handleChange}
                          placeholder="Ex : B204"
                          required
                        />
                      </div>

                      <div className="form-group">
                        <label>Classe</label>
                        <input
                          type="text"
                          name="classe"
                          value={formData.classe}
                          onChange={handleChange}
                          placeholder="Ex : LP MIAW"
                          required
                        />
                      </div>
                    </div>
                  </>
                )}

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

                <div className="form-actions">
                  <button
                    type="button"
                    className="secondary-btn"
                    onClick={handleCloseModal}
                  >
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

        <button className="fab" onClick={handleOpenModal}>
          +
        </button>
      </div>
    </div>
  );
}