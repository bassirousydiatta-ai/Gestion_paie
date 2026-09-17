import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';

const NAV_ITEMS = [
  { to: '/dashboard', label: 'Tableau de bord', icon: '📊', roles: ['admin', 'rh', 'comptable'] },
  { to: '/employes', label: 'Employés', icon: '👤', roles: ['admin', 'rh', 'comptable'] },
  { to: '/contrats', label: 'Contrats', icon: '📄', roles: ['admin', 'rh', 'comptable'] },
  { to: '/paies', label: 'Paies', icon: '💰', roles: ['admin', 'rh', 'comptable'] },
  { to: '/organisation', label: 'Organisation', icon: '🏢', roles: ['admin', 'rh'] },
  { to: '/catalogue', label: 'Primes & Cotisations', icon: '📋', roles: ['admin', 'comptable'] },
  { to: '/utilisateurs', label: 'Utilisateurs', icon: '🔑', roles: ['admin'] },
];

const ROLE_LABELS = {
  admin: 'Administrateur',
  rh: 'Ressources Humaines',
  comptable: 'Comptable',
};

export default function Layout() {
  const { user, logout, hasRole } = useAuth();
  const navigate = useNavigate();
  const [menuOuvert, setMenuOuvert] = useState(false);

  async function handleLogout() {
    await logout();
    setMenuOuvert(false);
    navigate('/login');
  }

  const items = NAV_ITEMS.filter((item) => hasRole(...item.roles));

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="mobile-header">
        <div>
          <p className="font-bold tracking-wide">ENTSI</p>
          <p className="text-xs text-primary-100">Gestion de Paie</p>
        </div>
        <button
          type="button"
          className="mobile-menu-button"
          onClick={() => setMenuOuvert((ouvert) => !ouvert)}
          aria-expanded={menuOuvert}
          aria-label={menuOuvert ? 'Fermer le menu' : 'Ouvrir le menu'}
        >
          {menuOuvert ? '✕' : '☰'}
        </button>
      </header>

      <div className="app-shell">
      <aside className={`app-sidebar ${menuOuvert ? 'is-open' : ''}`}>
        <div className="px-5 py-6 border-b border-white/10">
          <p className="font-bold text-lg tracking-wide">ENTSI</p>
          <p className="text-xs text-primary-100">Gestion de Paie</p>
        </div>

        <nav className="flex-1 px-3 py-4 space-y-1">
          {items.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              onClick={() => setMenuOuvert(false)}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                  isActive ? 'bg-white/15 text-white' : 'text-primary-100 hover:bg-white/10 hover:text-white'
                }`
              }
            >
              <span>{item.icon}</span>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="px-5 py-4 border-t border-white/10">
          <p className="text-sm font-medium">{user?.name}</p>
          <p className="text-xs text-primary-100">{ROLE_LABELS[user?.role] || user?.role}</p>
          <button onClick={handleLogout} className="mt-3 text-xs text-primary-100 hover:text-white underline">
            Se déconnecter
          </button>
        </div>
      </aside>

      <main className="app-main">
        <div className="max-w-6xl mx-auto p-4 sm:p-6 md:p-8">
          <Outlet />
        </div>
      </main>
      </div>
    </div>
  );
}
