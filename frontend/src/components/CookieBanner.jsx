import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import './CookieBanner.css';

export default function CookieBanner() {
  const [visible, setVisible] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    const consent = localStorage.getItem('cookie-consent');

    if (!consent) {
      const timer = setTimeout(() => {
        setMounted(true);
        setVisible(true);
      }, 1200);

      return () => clearTimeout(timer);
    }
  }, []);

  const handleAccept = () => {
    localStorage.setItem('cookie-consent', 'accepted');
    setVisible(false);
    setTimeout(() => setMounted(false), 300);
  };

  const handleRefuse = () => {
    localStorage.setItem('cookie-consent', 'refused');
    setVisible(false);
    setTimeout(() => setMounted(false), 300);
  };

  if (!mounted) return null;

  return (
    <div className={`cookie-banner ${visible ? 'show' : 'hide'}`}>
      <div className="cookie-content">
        <div className="cookie-text">
          <p>
            <span className="cookie-title">Cookies : </span>
            <span className="cookie-description">
              Nous utilisons des cookies pour améliorer votre expérience sur la plateforme.{' '}
              <Link to="/confidentialite" className="cookie-link">
                En savoir plus
              </Link>
            </span>
          </p>
        </div>

        <div className="cookie-actions">
          <button onClick={handleRefuse} className="cookie-btn secondary">
            Refuser
          </button>

          <button onClick={handleAccept} className="cookie-btn primary">
            Accepter
          </button>
        </div>
      </div>
    </div>
  );
}
