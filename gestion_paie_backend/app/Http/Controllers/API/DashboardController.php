<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Paie;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/statistiques
     * Accessible à tout utilisateur authentifié (Admin, RH, Comptable).
     */
    public function index(): JsonResponse
    {
        $anneeCourante = now()->year;

        return response()->json([
            'nombre_employes' => Employe::actifs()->count(),

            'masse_salariale_mois_courant' => (float) Paie::query()
                ->where('mois', now()->month)
                ->where('annee', $anneeCourante)
                ->where('statut', 'validee')
                ->sum('salaire_net'),

            'repartition_par_departement' => DB::table('employes')
                ->join('contrats', 'contrats.employe_id', '=', 'employes.id')
                ->join('postes', 'postes.id', '=', 'contrats.poste_id')
                ->join('departements', 'departements.id', '=', 'postes.departement_id')
                ->where('employes.statut', 'actif')
                ->select('departements.nom as departement', DB::raw('COUNT(DISTINCT employes.id) as total'))
                ->groupBy('departements.nom')
                ->get(),

            'evolution_masse_salariale' => Paie::query()
                ->where('annee', $anneeCourante)
                ->where('statut', 'validee')
                ->select('mois', DB::raw('SUM(salaire_net) as total'))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get(),

            'statistiques_mensuelles' => [
                'annee' => $anneeCourante,
                'mois' => now()->month,
                'nombre_paies_validees' => Paie::where('mois', now()->month)
                    ->where('annee', $anneeCourante)
                    ->where('statut', 'validee')
                    ->count(),
                'nombre_paies_en_brouillon' => Paie::where('mois', now()->month)
                    ->where('annee', $anneeCourante)
                    ->where('statut', 'brouillon')
                    ->count(),
            ],
        ]);
    }
}
