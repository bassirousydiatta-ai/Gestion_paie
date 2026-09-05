import { Link } from 'react-router-dom';

export function NotFoundPage() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center text-center px-4">
      <p className="text-6xl font-bold text-primary-600">404</p>
      <p className="text-slate-500 mt-2">Page introuvable.</p>
      <Link to="/dashboard" className="btn-primary mt-6">Retour au tableau de bord</Link>
    </div>
  );
}

export function ForbiddenPage() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center text-center px-4">
      <p className="text-6xl font-bold text-red-500">403</p>
      <p className="text-slate-500 mt-2">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
      <Link to="/dashboard" className="btn-primary mt-6">Retour au tableau de bord</Link>
    </div>
  );
}
