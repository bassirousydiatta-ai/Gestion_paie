import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { employesApi } from '../api/employes';
import { downloadFile } from '../api/client';
import AnomaliesAlert from '../components/AnomaliesAlert';

export default function EmployeDetailPage() {
  const { id } = useParams();
  const [employe, setEmploye] = useState(null);
  const [anomalies, setAnomalies] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    employesApi.consulter(id).then((res) => setEmploye(res.data)).finally(() => setLoading(false));
    employesApi.anomalies(id).then((res) => setAnomalies(res.data.anomalies || []));
  }, [id]);

  async function handleAttestation() {
    await downloadFile(`/employes/${id}/attestation-travail`, `attestation_${id}.pdf`);
  }

  if (loading) return <p className="text-slate-500">Chargement...</p>;
  if (!employe) return <p className="text-red-600">Employé introuvable.</p>;

  const contrat = employe.contrats?.[0];

  return (
    <div className="space-y-6">
      <div>
        <Link to="/employes" className="text-sm text-primary-600 hover:underline">
          ← Retour à la liste
        </Link>
        <div className="flex items-center justify-between mt-2">
          <h1 className="text-2xl font-bold text-slate-800">
            {employe.prenom} {employe.nom}
          </h1>
          <button className="btn-secondary" onClick={handleAttestation}>
            📄 Attestation de travail
          </button>
        </div>
      </div>

      <AnomaliesAlert anomalies={anomalies} />

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Informations personnelles</h2>
          <dl className="text-sm space-y-2">
            <div className="flex justify-between"><dt className="text-slate-500">CIN</dt><dd>{employe.cin}</dd></div>
            <div className="flex justify-between"><dt className="text-slate-500">Date de naissance</dt><dd>{new Date(employe.date_naissance).toLocaleDateString('fr-FR')}</dd></div>
            <div className="flex justify-between"><dt className="text-slate-500">Téléphone</dt><dd>{employe.telephone || '—'}</dd></div>
            <div className="flex justify-between"><dt className="text-slate-500">Adresse</dt><dd className="text-right">{employe.adresse || '—'}</dd></div>
            <div className="flex justify-between"><dt className="text-slate-500">Date d'embauche</dt><dd>{new Date(employe.date_embauche).toLocaleDateString('fr-FR')}</dd></div>
          </dl>
        </div>

        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-3">Contrat actuel</h2>
          {contrat ? (
            <dl className="text-sm space-y-2">
              <div className="flex justify-between"><dt className="text-slate-500">Poste</dt><dd>{contrat.poste?.intitule}</dd></div>
              <div className="flex justify-between"><dt className="text-slate-500">Département</dt><dd>{contrat.poste?.departement?.nom}</dd></div>
              <div className="flex justify-between"><dt className="text-slate-500">Type</dt><dd>{contrat.type_contrat}</dd></div>
              <div className="flex justify-between"><dt className="text-slate-500">Salaire de base</dt><dd>{Number(contrat.salaire_base).toLocaleString('fr-FR')} MAD</dd></div>
            </dl>
          ) : (
            <p className="text-sm text-slate-400">Aucun contrat enregistré.</p>
          )}
        </div>
      </div>

      <div className="card">
        <h2 className="font-semibold text-slate-700 mb-3">Historique des paies récentes</h2>
        {employe.paies?.length ? (
          <table className="data-table">
            <thead>
              <tr><th>Période</th><th>Brut</th><th>Net</th><th>Statut</th></tr>
            </thead>
            <tbody>
              {employe.paies.map((paie) => (
                <tr key={paie.id}>
                  <td>{String(paie.mois).padStart(2, '0')}/{paie.annee}</td>
                  <td>{Number(paie.salaire_brut).toLocaleString('fr-FR')} MAD</td>
                  <td>{Number(paie.salaire_net).toLocaleString('fr-FR')} MAD</td>
                  <td>
                    <span className={`badge ${paie.statut === 'validee' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                      {paie.statut === 'validee' ? 'Validée' : 'Brouillon'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <p className="text-sm text-slate-400">Aucune paie enregistrée pour le moment.</p>
        )}
      </div>
    </div>
  );
}
