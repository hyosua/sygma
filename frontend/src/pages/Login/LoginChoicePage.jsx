import React, { useState, useEffect } from 'react';
import { useNavigate } from "react-router-dom";
import "./LoginChoicePage.css";

export default function LoginChoicePage() {
    const navigate = useNavigate();

    return (
        <div className="login-choice-page">
            <div className="login-card">
                <div className="logo-container">
                    <img src="/logo.png" alt="Logo SYGMA" className="logo-image" />        
                </div>
                <h1 className="login-title">Bienvenue sur SYGMA</h1>
                <p className="login-subtitle">
                    Sélectionnez votre profil pour accéder à votre espace.
                </p>

                <div className="login-buttons">
                    <button
                        className="role-button"
                        onClick={() => navigate("/etudiant/mes-cours")}
                    >
                        Étudiant
                    </button>

                    <button
                        className="role-button"
                        onClick={() => navigate("/enseignant/mes-cours")}
                    >
                        Enseignant
                    </button>

                    <button
                        className="role-button secondary"
                        onClick={() => navigate("/gestionnaire")}
                    >
                        Gestionnaire
                    </button>
                </div>
            </div>
        </div>
    );
}