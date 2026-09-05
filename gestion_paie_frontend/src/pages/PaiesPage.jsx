import { useEffect, useState } from 'react';
import { paiesApi, bulletinsApi, primesApi } from '../api/paies';
import { employesApi } from '../api/employes';
import { exportsApi } from '../api/dashboard';
import { downloadFile } from '../api/client';
import { useAuth } from '../context/AuthContext';
import AnomaliesAlert from '../components/AnomaliesAlert';

const MOIS_NOMS = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

export default function PaiesPage() {
  const [paies, setPaies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [mois, setMois] = useState(new Date().getMonth() + 1);
  const [annee, setAnnee] = useState(new Date().getFullYear());
  const [modalOuvert, setModalOuvert] = useState(false);
  const { hasRole } = useAuth();
  const peutCalculer = hasRole('admin', 'comptable');

  async function charger() {
    setLoading(true);
    try {
      const res = await paiesApi.lister({ mois, annee });
      setPaies(res.data.data || res.data);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    charger();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mois, annee]);

  async function handleValider(id) {
    if (!confirm('Valider cette fiche de paie ? Elle ne pourra plus être modifiée.')) return;
    await paiesApi.valider(id);
    charger();
  }

  async function handleTelechargerBulletin(paie) {
    if (!paie.bulletin) return;
    await downloadFile(`/bulletins/${paie.bulletin.id}/pdf`, `bulletin_${paie.mois}_${paie.annee}.pdf`);
  }

  async function handleExportExcel() {
    await downloadFile(exportsApi.paiesExcelPath({ mois, annee }), `paies_${mois}_${annee}.xlsx`);
  }

  async function handleExportPdf() {
    await downloadFile(exportsApi.paiesPdfPath({ mois, annee }), `paies_${mois}_${annee}.pdf`);
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Paies</h1>
          <p className="text-slate-500 text-sm">Calcul et suivi des fiches de paie</p>
        </div>
        <div className="flex gap-2">
          <button className="btn-secondary" onClick={handleExportExcel}>⬇ Excel</button>
          <button className="btn-secondary" onClick={handleExportPdf}>⬇ PDF</button>
          {peutCalculer && (
            <button className="btn-primary" onClick={() => setModalOuvert(true)}>+ Calculer une paie</button>
          )}
        </div>
      </div>

      <div className="card">
        <div className="flex gap-3 mb-4">
          <select className="input max-w-[160px]" value={mois} onChange={(e) => setMois(Number(e.target.value))}>
            {MOIS_NOMS.map((nom, i) => <option key={i} value={i + 1}>{nom}</option>)}
          </select>
          <input
            type="number"
            className="input max-w-[120px]"
            value={annee}
            onChange={(e) => setAnnee(Number(e.target.value))}
          />
        </div>

        {loading ? (
          <p className="text-slate-500 text-sm py-6 text-center">Chargement...</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>Employé</th><th>Brut</th><th>Cotisations</th><th>IR</th><th>Net</th><th>Statut</th><th></th>
              </tr>
            </thead>
            <tbody>
              {paies.map((paie) => (
                <tr key={paie.id}>
                  <td>{paie.employe?.prenom} {paie.employe?.nom}</td>
                  <td>{Number(paie.salaire_brut).toLocaleString('fr-FR')} MAD</td>
                  <td>{Number(paie.total_cotisations).toLocaleString('fr-FR')} MAD</td>
                  <td>{Number(paie.impot_revenu).toLocaleString('fr-FR')} MAD</td>
                  <td className="font-semibold">{Number(paie.salaire_net).toLocaleString('fr-FR')} MAD</td>
                  <td>
                    <span className={`badge ${paie.statut === 'validee' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                      {paie.statut === 'validee' ? 'Validée' : 'Brouillon'}
                    </span>
                  </td>
                  <td className="text-right space-x-2 whitespace-nowrap">
                    {paie.statut === 'brouillon' && peutCalculer && (
                      <button className="text-xs text-emerald-600 hover:underline" onClick={() => handleValider(paie.id)}>
                        Valider
                      </button>
                    )}
                    {paie.bulletin && (
                      <button className="text-xs text-primary-600 hover:underline" onClick={() => handleTelechargerBulletin(paie)}>
                        Bulletin
                      </button>
                    )}
                  </td>
                </tr>
              ))}
              {paies.length === 0 && (
                <tr><td colSpan={7} className="text-center text-slate-400 py-6">Aucune paie pour cette période.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {modalOuvert && (
        <CalculPaieModal
          moisParDefaut={mois}
          anneeParDefaut={annee}
          onClose={() => setModalOuvert(false)}
          onCalculated={() => {
            setModalOuvert(false);
            charger();
          }}
        />
      )}
    </div>
  );
}

function CalculPaieModal({ moisParDefaut, anneeParDefaut, onClose, onCalculated }) {
  const [employes, setEmployes] = useState([]);
  const [primesCatalogue, setPrimesCatalogue] = useState([]);
  const [form, setForm] = useState({
    employe_id: '',
    mois: moisParDefaut,
    annee: anneeParDefaut,
    nombre_heures_supplementaires: 0,
    taux_majoration_heures_sup: 0.25,
  });
  const [primesSelectionnees, setPrimesSelectionnees] = useState([]);
  const [resultat, setResultat] = useState(null);
  const [anomalies, setAnomalies] = useState([]);
  const [erreurs, setErreurs] = useState({});
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    employesApi.lister({ statut: 'actif', par_page: 100 }).then((res) => setEmployes(res.data.data || res.data));
    primesApi.lister().then((res) => setPrimesCatalogue(res.data));
  }, []);

  function togglePrime(primeId, montantDefaut) {
    setPrimesSelectionnees((prev) => {
      const existe = prev.find((p) => p.prime_id === primeId);
      if (existe) return prev.filter((p) => p.prime_id !== primeId);
      return [...prev, { prime_id: primeId, montant_applique: montantDefaut }];
    });
  }

  async function handleCalculer(e) {
    e.preventDefault();
    setErreurs({});
    setSubmitting(true);
    try {
      const res = await paiesApi.calculer({
        ...form,
        primes: primesSelectionnees,
      });
      setResultat(res.data);
      const anomaliesRes = await paiesApi.anomalies(res.data.id);
      setAnomalies(anomaliesRes.data.anomalies || []);
    } catch (err) {
      if (err.response?.status === 422) setErreurs(err.response.data.errors || {});
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-xl shadow-lg w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 className="font-semibold text-slate-800">Calculer une paie</h2>
          <button onClick={onClose} className="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form onSubmit={handleCalculer} className="p-6 space-y-4">
          <div>
            <label className="label">Employé</label>
            <select className="input" value={form.employe_id} onChange={(e) => setForm((f) => ({ ...f, employe_id: e.target.value }))} required>
              <option value="">Sélectionner...</option>
              {employes.map((e) => <option key={e.id} value={e.id}>{e.prenom} {e.nom}</option>)}
            </select>
            {erreurs.employe_id && <p className="text-red-600 text-xs mt-1">{erreurs.employe_id[0]}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Mois</label>
              <input type="number" min="1" max="12" className="input" value={form.mois} onChange={(e) => setForm((f) => ({ ...f, mois: Number(e.target.value) }))} />
            </div>
            <div>
              <label className="label">Année</label>
              <input type="number" className="input" value={form.annee} onChange={(e) => setForm((f) => ({ ...f, annee: Number(e.target.value) }))} />
            </div>
          </div>

          <div>
            <label className="label">Primes</label>
            <div className="space-y-2">
              {primesCatalogue.map((prime) => (
                <label key={prime.id} className="flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    onChange={() => togglePrime(prime.id, Number(prime.montant))}
                    checked={primesSelectionnees.some((p) => p.prime_id === prime.id)}
                  />
                  {prime.libelle} ({prime.type === 'fixe' ? `${prime.montant} MAD` : `${prime.montant}%`})
                </label>
              ))}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Heures supplémentaires</label>
              <input
                type="number"
                min="0"
                className="input"
                value={form.nombre_heures_supplementaires}
                onChange={(e) => setForm((f) => ({ ...f, nombre_heures_supplementaires: Number(e.target.value) }))}
              />
            </div>
            <div>
              <label className="label">Majoration</label>
              <select
                className="input"
                value={form.taux_majoration_heures_sup}
                onChange={(e) => setForm((f) => ({ ...f, taux_majoration_heures_sup: Number(e.target.value) }))}
              >
                <option value={0.25}>+25% (jour)</option>
                <option value={0.5}>+50% (nuit)</option>
                <option value={1}>+100% (férié)</option>
              </select>
            </div>
          </div>

          {resultat && (
            <div className="bg-primary-50 border border-primary-100 rounded-lg p-4 text-sm space-y-1">
              <div className="flex justify-between"><span>Salaire brut</span><strong>{Number(resultat.salaire_brut).toLocaleString('fr-FR')} MAD</strong></div>
              <div className="flex justify-between"><span>Cotisations</span><strong>- {Number(resultat.total_cotisations).toLocaleString('fr-FR')} MAD</strong></div>
              <div className="flex justify-between"><span>Impôt sur le revenu</span><strong>- {Number(resultat.impot_revenu).toLocaleString('fr-FR')} MAD</strong></div>
              <div className="flex justify-between text-primary-700 text-base pt-1 border-t border-primary-100"><span>Salaire net</span><strong>{Number(resultat.salaire_net).toLocaleString('fr-FR')} MAD</strong></div>
            </div>
          )}

          <AnomaliesAlert anomalies={anomalies} />

          <div className="flex justify-end gap-3 pt-2">
            <button type="button" className="btn-secondary" onClick={onClose}>Fermer</button>
            {resultat ? (
              <button type="button" className="btn-primary" onClick={onCalculated}>Terminer</button>
            ) : (
              <button type="submit" className="btn-primary" disabled={submitting}>
                {submitting ? 'Calcul...' : 'Calculer'}
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}
