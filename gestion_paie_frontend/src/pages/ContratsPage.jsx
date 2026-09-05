import { useEffect, useState } from 'react';
import { contratsApi, postesApi } from '../api/organisation';
import { employesApi } from '../api/employes';
import { useAuth } from '../context/AuthContext';

export default function ContratsPage() {
  const [contrats, setContrats] = useState([]);
  const [employes, setEmployes] = useState([]);
  const [postes, setPostes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const { hasRole } = useAuth();
  const peutGerer = hasRole('admin', 'rh');

  async function charger() {
    setLoading(true);
    try {
      const res = await contratsApi.lister();
      setContrats(res.data.data || res.data);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    charger();
    employesApi.lister({ statut: 'actif', par_page: 100 }).then((res) => setEmployes(res.data.data || res.data));
    postesApi.lister().then((res) => setPostes(res.data));
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Contrats</h1>
          <p className="text-slate-500 text-sm">Contrats de travail des employés</p>
        </div>
        {peutGerer && (
          <button className="btn-primary" onClick={() => setModalOuvert(true)}>
            + Nouveau contrat
          </button>
        )}
      </div>

      <div className="card">
        {loading ? (
          <p className="text-slate-500 text-sm py-6 text-center">Chargement...</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Employé</th><th>Poste</th><th>Type</th><th>Salaire de base</th><th>Début</th><th>Fin</th>
              </tr>
            </thead>
            <tbody>
              {contrats.map((contrat) => (
                <tr key={contrat.id}>
                  <td>{contrat.employe?.prenom} {contrat.employe?.nom}</td>
                  <td>{contrat.poste?.intitule}</td>
                  <td><span className="badge bg-primary-50 text-primary-600">{contrat.type_contrat}</span></td>
                  <td>{Number(contrat.salaire_base).toLocaleString('fr-FR')} MAD</td>
                  <td>{new Date(contrat.date_debut).toLocaleDateString('fr-FR')}</td>
                  <td>{contrat.date_fin ? new Date(contrat.date_fin).toLocaleDateString('fr-FR') : '—'}</td>
                </tr>
              ))}
              {contrats.length === 0 && (
                <tr><td colSpan={6} className="text-center text-slate-400 py-6">Aucun contrat.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {modalOuvert && (
        <ContratFormModal
          employes={employes}
          postes={postes}
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

function ContratFormModal({ employes, postes, onClose, onCreated }) {
  const [form, setForm] = useState({
    employe_id: '', poste_id: '', type_contrat: 'CDI', salaire_base: '', date_debut: '',
  });
  const [erreurs, setErreurs] = useState({});
  const [submitting, setSubmitting] = useState(false);

  function update(champ, valeur) {
    setForm((f) => ({ ...f, [champ]: valeur }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setSubmitting(true);
    try {
      await contratsApi.creer(form);
      onCreated();
    } catch (err) {
      if (err.response?.status === 422) setErreurs(err.response.data.errors || {});
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-xl shadow-lg w-full max-w-lg">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 className="font-semibold text-slate-800">Nouveau contrat</h2>
          <button onClick={onClose} className="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <div>
            <label className="label">Employé</label>
            <select className="input" value={form.employe_id} onChange={(e) => update('employe_id', e.target.value)} required>
              <option value="">Sélectionner...</option>
              {employes.map((e) => <option key={e.id} value={e.id}>{e.prenom} {e.nom}</option>)}
            </select>
            {erreurs.employe_id && <p className="text-red-600 text-xs mt-1">{erreurs.employe_id[0]}</p>}
          </div>

          <div>
            <label className="label">Poste</label>
            <select className="input" value={form.poste_id} onChange={(e) => update('poste_id', e.target.value)} required>
              <option value="">Sélectionner...</option>
              {postes.map((p) => <option key={p.id} value={p.id}>{p.intitule}</option>)}
            </select>
            {erreurs.poste_id && <p className="text-red-600 text-xs mt-1">{erreurs.poste_id[0]}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Type de contrat</label>
              <select className="input" value={form.type_contrat} onChange={(e) => update('type_contrat', e.target.value)}>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Stage">Stage</option>
                <option value="Interim">Intérim</option>
              </select>
            </div>
            <div>
              <label className="label">Salaire de base (MAD)</label>
              <input type="number" className="input" value={form.salaire_base} onChange={(e) => update('salaire_base', e.target.value)} required />
              {erreurs.salaire_base && <p className="text-red-600 text-xs mt-1">{erreurs.salaire_base[0]}</p>}
            </div>
          </div>

          <div>
            <label className="label">Date de début</label>
            <input type="date" className="input" value={form.date_debut} onChange={(e) => update('date_debut', e.target.value)} required />
            {erreurs.date_debut && <p className="text-red-600 text-xs mt-1">{erreurs.date_debut[0]}</p>}
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
