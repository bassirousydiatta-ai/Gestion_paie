import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { employesApi } from '../api/employes';
import { useAuth } from '../context/AuthContext';
import EmployeFormModal from './EmployeFormModal';

export default function EmployesPage() {
  const [employes, setEmployes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [recherche, setRecherche] = useState('');
  const [statut, setStatut] = useState('actif');
  const [modalOuvert, setModalOuvert] = useState(false);
  const { hasRole } = useAuth();

  const peutGerer = hasRole('admin', 'rh');

  async function charger() {
    setLoading(true);
    try {
      const res = await employesApi.lister({ recherche, statut: statut || undefined });
      setEmployes(res.data.data || res.data);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    const timeout = setTimeout(charger, 300); // debounce recherche
    return () => clearTimeout(timeout);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [recherche, statut]);

  async function handleArchiver(id) {
    if (!confirm('Archiver cet employé ?')) return;
    await employesApi.archiver(id);
    charger();
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Employés</h1>
          <p className="text-slate-500 text-sm">Gestion des employés et de leurs informations</p>
        </div>
        {peutGerer && (
          <button className="btn-primary" onClick={() => setModalOuvert(true)}>
            + Nouvel employé
          </button>
        )}
      </div>

      <div className="card">
        <div className="flex gap-3 mb-4">
          <input
            type="text"
            placeholder="Rechercher par nom, prénom, CIN..."
            className="input max-w-sm"
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
          <select className="input max-w-[160px]" value={statut} onChange={(e) => setStatut(e.target.value)}>
            <option value="actif">Actifs</option>
            <option value="archive">Archivés</option>
            <option value="">Tous</option>
          </select>
        </div>

        {loading ? (
          <p className="text-slate-500 text-sm py-6 text-center">Chargement...</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>CIN</th>
                <th>Date d'embauche</th>
                <th>Statut</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {employes.map((employe) => (
                <tr key={employe.id}>
                  <td>
                    <Link to={`/employes/${employe.id}`} className="text-primary-600 font-medium hover:underline">
                      {employe.prenom} {employe.nom}
                    </Link>
                  </td>
                  <td>{employe.cin}</td>
                  <td>{new Date(employe.date_embauche).toLocaleDateString('fr-FR')}</td>
                  <td>
                    <span className={`badge ${employe.statut === 'actif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                      {employe.statut === 'actif' ? 'Actif' : 'Archivé'}
                    </span>
                  </td>
                  <td className="text-right">
                    {peutGerer && employe.statut === 'actif' && (
                      <button className="text-xs text-slate-500 hover:text-red-600" onClick={() => handleArchiver(employe.id)}>
                        Archiver
                      </button>
                    )}
                  </td>
                </tr>
              ))}
              {employes.length === 0 && (
                <tr>
                  <td colSpan={5} className="text-center text-slate-400 py-6">
                    Aucun employé trouvé.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {modalOuvert && (
        <EmployeFormModal
          onClose={() => setModalOuvert(false)}
          onCreated={() => {
            setModalOuvert(false);
            charger();
          }}
        />
      )}
    </div>
  );
}
