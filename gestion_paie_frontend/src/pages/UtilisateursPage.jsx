import { useEffect, useState } from 'react';
import { usersApi } from '../api/dashboard';

const ROLE_LABELS = { admin: 'Administrateur', rh: 'RH', comptable: 'Comptable' };

export default function UtilisateursPage() {
  const [users, setUsers] = useState([]);
  const [modalOuvert, setModalOuvert] = useState(false);

  function charger() {
    usersApi.lister().then((res) => setUsers(res.data));
  }

  useEffect(charger, []);

  async function handleSupprimer(id) {
    if (!confirm('Supprimer ce compte utilisateur ?')) return;
    try {
      await usersApi.supprimer(id);
      charger();
    } catch (err) {
      alert(err.response?.data?.message || 'Suppression impossible.');
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Utilisateurs</h1>
          <p className="text-slate-500 text-sm">Comptes ayant accès à l'application</p>
        </div>
        <button className="btn-primary" onClick={() => setModalOuvert(true)}>+ Nouveau compte</button>
      </div>

      <div className="card">
        <table className="data-table">
          <thead>
            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th></th></tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id}>
                <td>{u.name}</td>
                <td>{u.email}</td>
                <td><span className="badge bg-primary-50 text-primary-600">{ROLE_LABELS[u.role]}</span></td>
                <td className="text-right">
                  <button className="text-xs text-red-500 hover:underline" onClick={() => handleSupprimer(u.id)}>
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {modalOuvert && (
        <NouvelUtilisateurModal onClose={() => setModalOuvert(false)} onCreated={() => { setModalOuvert(false); charger(); }} />
      )}
    </div>
  );
}

function NouvelUtilisateurModal({ onClose, onCreated }) {
  const [form, setForm] = useState({ name: '', email: '', password: '', role: 'rh' });
  const [erreurs, setErreurs] = useState({});
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    try {
      await usersApi.creer(form);
      onCreated();
    } catch (err) {
      if (err.response?.status === 422) setErreurs(err.response.data.errors || {});
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-xl shadow-lg w-full max-w-md">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 className="font-semibold text-slate-800">Nouveau compte</h2>
          <button onClick={onClose} className="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <div>
            <label className="label">Nom complet</label>
            <input className="input" value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} required />
          </div>
          <div>
            <label className="label">Email</label>
            <input type="email" className="input" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} required />
            {erreurs.email && <p className="text-red-600 text-xs mt-1">{erreurs.email[0]}</p>}
          </div>
          <div>
            <label className="label">Mot de passe</label>
            <input type="password" className="input" value={form.password} onChange={(e) => setForm((f) => ({ ...f, password: e.target.value }))} required />
            {erreurs.password && <p className="text-red-600 text-xs mt-1">{erreurs.password[0]}</p>}
          </div>
          <div>
            <label className="label">Rôle</label>
            <select className="input" value={form.role} onChange={(e) => setForm((f) => ({ ...f, role: e.target.value }))}>
              <option value="rh">RH</option>
              <option value="comptable">Comptable</option>
              <option value="admin">Administrateur</option>
            </select>
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <button type="button" className="btn-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn-primary" disabled={submitting}>
              {submitting ? 'Création...' : 'Créer'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
