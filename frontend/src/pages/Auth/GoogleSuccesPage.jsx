import { useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';

const REDIRECTIONS = {
  etudiant: '/etudiant/mes-cours',
  enseignant: '/enseignant/mes-cours',
  gestionnaire: '/gestionnaire',
};

export default function GoogleSuccesPage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  useEffect(() => {
    const token = searchParams.get('token');
    if (!token) {
      navigate('/login');
      return;
    }

    const recupererUtilisateur = async () => {
      try {
        const res = await fetch(`${import.meta.env.VITE_API_URL}/user`, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
        });
        const user = await res.json();

        if (res.ok) {
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));

          const roles = user.roles?.map((r) => r.name) ?? [];
          const role = roles[0] ?? 'etudiant';
          navigate(REDIRECTIONS[role] ?? '/login');
        } else {
          navigate('/login');
        }
      } catch {
        navigate('/login');
      }
    };

    recupererUtilisateur();
  }, [searchParams, navigate]);

  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100vh',
      }}
    >
      <h1 style={{ fontSize: '2rem', marginBottom: '1rem' }}>Connexion réussie !</h1>
      <p style={{ fontSize: '1.2rem' }}>Vous allez être redirigé automatiquement.</p>
    </div>
  );
}
