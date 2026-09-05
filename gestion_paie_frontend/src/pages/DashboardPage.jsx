import { useEffect, useState } from 'react';
import { LineChart, Line, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';
import { dashboardApi } from '../api/dashboard';

const MOIS_LABELS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

function StatCard({ label, value, accent }) {
  return (
    <div className="card">
      <p className="text-sm text-slate-500">{label}</p>
      <p className={`text-2xl font-bold mt-1 ${accent || 'text-slate-800'}`}>{value}</p>
    </div>
  );
}

export default function DashboardPage() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    dashboardApi
      .statistiques()
      .then((res) => setStats(res.data))
      .catch(() => setError('Impossible de charger les statistiques.'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <p className="text-slate-500">Chargement...</p>;
  if (error) return <p className="text-red-600">{error}</p>;
  if (!stats) return null;

  const evolutionData = (stats.evolution_masse_salariale || []).map((item) => ({
    mois: MOIS_LABELS[item.mois - 1],
    total: Number(item.total),
  }));

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">Tableau de bord</h1>
        <p className="text-slate-500 text-sm">Vue d'ensemble de la gestion de la paie</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Employés actifs" value={stats.nombre_employes} />
        <StatCard
          label="Masse salariale (mois en cours)"
          value={`${Number(stats.masse_salariale_mois_courant).toLocaleString('fr-FR')} MAD`}
          accent="text-primary-600"
        />
        <StatCard label="Paies validées ce mois" value={stats.statistiques_mensuelles.nombre_paies_validees} accent="text-emerald-600" />
        <StatCard label="Paies en brouillon" value={stats.statistiques_mensuelles.nombre_paies_en_brouillon} accent="text-amber-600" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-4">Évolution de la masse salariale</h2>
          <ResponsiveContainer width="100%" height={260}>
            <LineChart data={evolutionData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#eee" />
              <XAxis dataKey="mois" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} />
              <Tooltip formatter={(v) => `${v.toLocaleString('fr-FR')} MAD`} />
              <Line type="monotone" dataKey="total" stroke="#2E74B5" strokeWidth={2} dot={{ r: 3 }} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        <div className="card">
          <h2 className="font-semibold text-slate-700 mb-4">Répartition par département</h2>
          <div className="space-y-3">
            {(stats.repartition_par_departement || []).map((dep) => (
              <div key={dep.departement} className="flex items-center gap-3">
                <span className="text-sm text-slate-600 w-40 truncate">{dep.departement}</span>
                <div className="flex-1 bg-slate-100 rounded-full h-2.5">
                  <div
                    className="bg-primary-500 h-2.5 rounded-full"
                    style={{
                      width: `${Math.min(100, (dep.total / stats.nombre_employes) * 100)}%`,
                    }}
                  />
                </div>
                <span className="text-sm font-medium text-slate-700 w-6 text-right">{dep.total}</span>
              </div>
            ))}
            {(!stats.repartition_par_departement || stats.repartition_par_departement.length === 0) && (
              <p className="text-sm text-slate-400">Aucune donnée disponible.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
