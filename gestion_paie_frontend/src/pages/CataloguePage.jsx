import { useEffect, useState } from 'react';
import { primesApi, cotisationsApi } from '../api/paies';

export default function CataloguePage() {
  const [primes, setPrimes] = useState([]);
  const [cotisations, setCotisations] = useState([]);
  const [nouvellePrime, setNouvellePrime] = useState({ libelle: '', montant: '', type: 'fixe' });
  const [nouvelleCotisation, setNouvelleCotisation] = useState({ nom: '', taux: '', plafond: '' });

  function charger() {
    primesApi.lister().then((res) => setPrimes(res.data));
    cotisationsApi.lister().then((res) => setCotisations(res.data));
  }

  useEffect(charger, []);

  async function handleAjouterPrime(e) {
    e.preventDefault();
    await primesApi.creer(nouvellePrime);
    setNouvellePrime({ libelle: '', montant: '', type: 'fixe' });
    charger();
  }

  async function handleAjouterCotisation(e) {
    e.preventDefault();
    await cotisationsApi.creer({
      ...nouvelleCotisation,
      plafond: nouvelleCotisation.plafond || null,
    });
    setNouvelleCotisation({ nom: '', taux: '', plafond: '' });
    charger();
  }

  async function handleSupprimerPrime(id) {
    if (!confirm('Supprimer cette prime ?')) return;
    await primesApi.supprimer(id);
    charger();
  }

  async function handleSupprimerCotisation(id) {
    if (!confirm('Supprimer cette cotisation ?')) return;
    await cotisationsApi.supprimer(id);
    charger();
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">Primes & Cotisations</h1>
        <p className="text-slate-500 text-sm">Catalogues utilisés lors du calcul de la paie</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Primes</h2>
          <ul className="divide-y divide-slate-100 mb-4">
            {primes.map((p) => (
              <li key={p.id} className="py-2 flex justify-between items-center text-sm">
                <span>{p.libelle} <span className="text-slate-400">({p.type === 'fixe' ? `${p.montant} MAD` : `${p.montant}%`})</span></span>
                <button className="text-xs text-red-500 hover:underline" onClick={() => handleSupprimerPrime(p.id)}>Supprimer</button>
              </li>
            ))}
          </ul>
          <form onSubmit={handleAjouterPrime} className="space-y-2">
            <input className="input" placeholder="Libellé" value={nouvellePrime.libelle} onChange={(e) => setNouvellePrime((f) => ({ ...f, libelle: e.target.value }))} required />
            <div className="flex gap-2">
              <input type="number" step="0.01" className="input" placeholder="Montant" value={nouvellePrime.montant} onChange={(e) => setNouvellePrime((f) => ({ ...f, montant: e.target.value }))} required />
              <select className="input" value={nouvellePrime.type} onChange={(e) => setNouvellePrime((f) => ({ ...f, type: e.target.value }))}>
                <option value="fixe">Fixe (MAD)</option>
                <option value="pourcentage">Pourcentage (%)</option>
              </select>
            </div>
            <button className="btn-primary w-full">Ajouter la prime</button>
          </form>
        </div>

        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Cotisations</h2>
          <ul className="divide-y divide-slate-100 mb-4">
            {cotisations.map((c) => (
              <li key={c.id} className="py-2 flex justify-between items-center text-sm">
                <span>{c.nom} <span className="text-slate-400">({c.taux}%{c.plafond ? `, plafond ${c.plafond} MAD` : ''})</span></span>
                <button className="text-xs text-red-500 hover:underline" onClick={() => handleSupprimerCotisation(c.id)}>Supprimer</button>
              </li>
            ))}
          </ul>
          <form onSubmit={handleAjouterCotisation} className="space-y-2">
            <input className="input" placeholder="Nom (ex. CNSS)" value={nouvelleCotisation.nom} onChange={(e) => setNouvelleCotisation((f) => ({ ...f, nom: e.target.value }))} required />
            <div className="flex gap-2">
              <input type="number" step="0.01" className="input" placeholder="Taux (%)" value={nouvelleCotisation.taux} onChange={(e) => setNouvelleCotisation((f) => ({ ...f, taux: e.target.value }))} required />
              <input type="number" step="0.01" className="input" placeholder="Plafond (optionnel)" value={nouvelleCotisation.plafond} onChange={(e) => setNouvelleCotisation((f) => ({ ...f, plafond: e.target.value }))} />
            </div>
            <button className="btn-primary w-full">Ajouter la cotisation</button>
          </form>
        </div>
      </div>
    </div>
  );
}
