import { useState } from 'react';
import { employesApi } from '../api/employes';
import AnomaliesAlert from '../components/AnomaliesAlert';

const CHAMPS_VIDES = {
  nom: '',
  prenom: '',
  cin: '',
  date_naissance: '',
  adresse: '',
  telephone: '',
  date_embauche: '',
};

export default function EmployeFormModal({ onClose, onCreated }) {
  const [form, setForm] = useState(CHAMPS_VIDES);
  const [erreurs, setErreurs] = useState({});
  const [anomalies, setAnomalies] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  function update(champ, valeur) {
    setForm((f) => ({ ...f, [champ]: valeur }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setErreurs({});
    setSubmitting(true);
    try {
      const res = await employesApi.creer(form);
      setAnomalies(res.data.anomalies || []);

      // Si des anomalies non bloquantes existent, on les montre 2 secondes avant de fermer
      if (res.data.anomalies?.length) {
        setTimeout(() => onCreated(), 1800);
      } else {
        onCreated();
      }
    } catch (err) {
      if (err.response?.status === 422) {
        setErreurs(err.response.data.errors || {});
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 className="font-semibold text-slate-800">Nouvel employé</h2>
          <button onClick={onClose} className="text-slate-400 hover:text-slate-600">
            ✕
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <AnomaliesAlert anomalies={anomalies} />

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Nom</label>
              <input className="input" value={form.nom} onChange={(e) => update('nom', e.target.value)} required />
              {erreurs.nom && <p className="text-red-600 text-xs mt-1">{erreurs.nom[0]}</p>}
            </div>
            <div>
              <label className="label">Prénom</label>
              <input className="input" value={form.prenom} onChange={(e) => update('prenom', e.target.value)} required />
              {erreurs.prenom && <p className="text-red-600 text-xs mt-1">{erreurs.prenom[0]}</p>}
            </div>
          </div>

          <div>
            <label className="label">CIN</label>
            <input className="input" value={form.cin} onChange={(e) => update('cin', e.target.value)} required />
            {erreurs.cin && <p className="text-red-600 text-xs mt-1">{erreurs.cin[0]}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Date de naissance</label>
              <input
                type="date"
                className="input"
                value={form.date_naissance}
                onChange={(e) => update('date_naissance', e.target.value)}
                required
              />
              {erreurs.date_naissance && <p className="text-red-600 text-xs mt-1">{erreurs.date_naissance[0]}</p>}
            </div>
            <div>
              <label className="label">Date d'embauche</label>
              <input
                type="date"
                className="input"
                value={form.date_embauche}
                onChange={(e) => update('date_embauche', e.target.value)}
                required
              />
              {erreurs.date_embauche && <p className="text-red-600 text-xs mt-1">{erreurs.date_embauche[0]}</p>}
            </div>
          </div>

          <div>
            <label className="label">Adresse</label>
            <input className="input" value={form.adresse} onChange={(e) => update('adresse', e.target.value)} />
          </div>

          <div>
            <label className="label">Téléphone</label>
            <input className="input" value={form.telephone} onChange={(e) => update('telephone', e.target.value)} />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <button type="button" className="btn-secondary" onClick={onClose}>
              Annuler
            </button>
            <button type="submit" className="btn-primary" disabled={submitting}>
              {submitting ? 'Création...' : 'Créer'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
