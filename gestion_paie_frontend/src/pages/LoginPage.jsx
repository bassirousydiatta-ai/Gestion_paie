import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function LoginPage() {
  const [mode, setMode] = useState('login');
  const [name, setName] = useState('');
  const [role, setRole] = useState('rh');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const { login, register } = useAuth();
  const navigate = useNavigate();

  async function handleSubmit(e) {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      if (mode === 'register') {
        await register(name, email, password, passwordConfirmation, role);
      } else {
        await login(email, password);
      }
      navigate('/dashboard');
    } catch (err) {
      const validationErrors = err.response?.data?.errors;
      const firstValidationError = validationErrors && Object.values(validationErrors)[0]?.[0];
      const status = err.response?.status;
      const message = err.response?.data?.message;
      setError(
        firstValidationError ||
          message ||
          (status === 404
            ? "L'API backend est introuvable. Vérifiez l'URL Railway configurée dans Netlify."
            : !err.response
              ? "Impossible de joindre l'API. Vérifiez l'URL Railway et la configuration CORS."
              : 'Une erreur est survenue.')
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-sm">
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold text-primary-600">ENTSI</h1>
          <p className="text-slate-500 text-sm">Application de Gestion de Paie</p>
        </div>

        <form onSubmit={handleSubmit} className="card space-y-4">
          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-3 py-2">
              {error}
            </div>
          )}

          {mode === 'register' && (
            <div>
              <label className="label">Nom complet</label>
              <input
                type="text"
                className="input"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
                autoFocus
              />
            </div>
          )}

          {mode === 'register' && (
            <div>
              <label className="label">Type de compte</label>
              <select className="input" value={role} onChange={(e) => setRole(e.target.value)} required>
                <option value="rh">Ressources humaines</option>
                <option value="comptable">Comptable</option>
              </select>
            </div>
          )}

          <div>
            <label className="label">Email</label>
            <input
              type="email"
              className="input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus={mode === 'login'}
            />
          </div>

          <div>
            <label className="label">Mot de passe</label>
            <div className="relative">
              <input
                type={showPassword ? 'text' : 'password'}
                className="input pr-12"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-primary-600"
                onClick={() => setShowPassword((visible) => !visible)}
                aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                title={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
              >
                {showPassword ? '◉' : '◌'}
              </button>
            </div>
          </div>

          {mode === 'register' && (
            <div>
              <label className="label">Confirmer le mot de passe</label>
              <div className="relative">
                <input
                  type={showPasswordConfirmation ? 'text' : 'password'}
                  className="input pr-12"
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  required
                />
                <button
                  type="button"
                  className="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-primary-600"
                  onClick={() => setShowPasswordConfirmation((visible) => !visible)}
                  aria-label={showPasswordConfirmation ? 'Masquer la confirmation' : 'Afficher la confirmation'}
                  title={showPasswordConfirmation ? 'Masquer la confirmation' : 'Afficher la confirmation'}
                >
                  {showPasswordConfirmation ? '◉' : '◌'}
                </button>
              </div>
            </div>
          )}

          <button type="submit" className="btn-primary w-full" disabled={submitting}>
            {submitting ? 'Création...' : mode === 'register' ? 'Créer un compte' : 'Se connecter'}
          </button>

          <p className="text-center text-sm text-slate-500">
            {mode === 'register' ? 'Vous avez déjà un compte ?' : "Vous n'avez pas encore de compte ?"}{' '}
            <button
              type="button"
              className="font-medium text-primary-600 hover:underline"
              onClick={() => {
                setMode(mode === 'register' ? 'login' : 'register');
                setError(null);
              }}
            >
              {mode === 'register' ? 'Se connecter' : 'Créer un compte'}
            </button>
          </p>
        </form>
      </div>
    </div>
  );
}
