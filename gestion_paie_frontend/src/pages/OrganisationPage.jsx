import { useEffect, useState } from 'react';
import { departementsApi, postesApi } from '../api/organisation';

export default function OrganisationPage() {
  const [departements, setDepartements] = useState([]);
  const [postes, setPostes] = useState([]);
  const [nouveauDepartement, setNouveauDepartement] = useState('');
  const [nouveauPoste, setNouveauPoste] = useState({ departement_id: '', intitule: '' });

  function charger() {
    departementsApi.lister().then((res) => setDepartements(res.data));
    postesApi.lister().then((res) => setPostes(res.data));
  }

  useEffect(charger, []);

  async function handleAjouterDepartement(e) {
    e.preventDefault();
    if (!nouveauDepartement.trim()) return;
    await departementsApi.creer({ nom: nouveauDepartement });
    setNouveauDepartement('');
    charger();
  }

  async function handleAjouterPoste(e) {
    e.preventDefault();
    if (!nouveauPoste.departement_id || !nouveauPoste.intitule.trim()) return;
    await postesApi.creer(nouveauPoste);
    setNouveauPoste({ departement_id: '', intitule: '' });
    charger();
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">Organisation</h1>
        <p className="text-slate-500 text-sm">Départements et postes de l'entreprise</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Départements</h2>
          <ul className="divide-y divide-slate-100 mb-4">
            {departements.map((d) => (
              <li key={d.id} className="py-2 flex justify-between text-sm">
                <span>{d.nom}</span>
                <span className="text-slate-400">{d.postes_count ?? 0} poste(s)</span>
              </li>
            ))}
          </ul>
          <form onSubmit={handleAjouterDepartement} className="flex gap-2">
            <input
              className="input"
              placeholder="Nom du département"
              value={nouveauDepartement}
              onChange={(e) => setNouveauDepartement(e.target.value)}
            />
            <button className="btn-primary shrink-0">Ajouter</button>
          </form>
        </div>

        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Postes</h2>
          <ul className="divide-y divide-slate-100 mb-4">
            {postes.map((p) => (
              <li key={p.id} className="py-2 flex justify-between text-sm">
                <span>{p.intitule}</span>
                <span className="text-slate-400">{p.departement?.nom}</span>
              </li>
            ))}
          </ul>
          <form onSubmit={handleAjouterPoste} className="space-y-2">
            <select
              className="input"
              value={nouveauPoste.departement_id}
              onChange={(e) => setNouveauPoste((p) => ({ ...p, departement_id: e.target.value }))}
            >
              <option value="">Département...</option>
              {departements.map((d) => <option key={d.id} value={d.id}>{d.nom}</option>)}
            </select>
            <div className="flex gap-2">
              <input
                className="input"
                placeholder="Intitulé du poste"
                value={nouveauPoste.intitule}
                onChange={(e) => setNouveauPoste((p) => ({ ...p, intitule: e.target.value }))}
              />
              <button className="btn-primary shrink-0">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
