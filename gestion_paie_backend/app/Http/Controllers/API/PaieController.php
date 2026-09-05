<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Paie;
use App\Services\PaieCalculService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaieController extends Controller
{
    public function __construct(
        private readonly PaieCalculService $paieCalculService,
        private readonly PdfGeneratorService $pdfGeneratorService,
    ) {
    }

    /**
     * GET /api/paies?employe_id=&mois=&annee=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Paie::class);

        $paies = Paie::query()
            ->with(['employe', 'bulletin'])
            ->when($request->filled('employe_id'), fn ($q) => $q->where('employe_id', $request->integer('employe_id')))
            ->when($request->filled('mois'), fn ($q) => $q->where('mois', $request->integer('mois')))
            ->when($request->filled('annee'), fn ($q) => $q->where('annee', $request->integer('annee')))
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate($request->integer('par_page', 15));

        return response()->json($paies);
    }

    /**
     * GET /api/paies/{paie}
     */
    public function show(Paie $paie): JsonResponse
    {
        $this->authorize('view', $paie);

        return response()->json(
            $paie->load(['employe', 'primes', 'cotisations', 'retenues', 'bulletin'])
        );
    }

    /**
     * POST /api/paies/calculer
     * Calcule (ou recalcule, si en brouillon) la fiche de paie d'un employé pour une période donnée.
     *
     * Body attendu :
     * {
     *   "employe_id": 3,
     *   "mois": 7,
     *   "annee": 2026,
     *   "primes": [ { "prime_id": 1, "montant_applique": 500 } ],       // optionnel
     *   "retenues": [ { "libelle": "Absence", "montant": 200 } ],      // optionnel
     *   "nombre_heures_supplementaires": 6,                            // optionnel, défaut 0
     *   "taux_majoration_heures_sup": 0.25                             // optionnel, défaut 0.25 (+25%)
     * }
     */
    public function calculer(Request $request): JsonResponse
    {
        $this->authorize('create', Paie::class);

        $data = $request->validate([
            'employe_id' => ['required', 'exists:employes,id'],
            'mois' => ['required', 'integer', 'between:1,12'],
            'annee' => ['required', 'integer', 'min:2000'],
            'primes' => ['sometimes', 'array'],
            'primes.*.prime_id' => ['required_with:primes', 'exists:primes,id'],
            'primes.*.montant_applique' => ['required_with:primes', 'numeric', 'min:0'],
            'retenues' => ['sometimes', 'array'],
            'retenues.*.libelle' => ['required_with:retenues', 'string', 'max:100'],
            'retenues.*.montant' => ['required_with:retenues', 'numeric', 'min:0'],
            'nombre_heures_supplementaires' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            // 0.25 = +25% (heures de jour), 0.50 = +50% (heures de nuit), 1.00 = +100% (jour férié)
            'taux_majoration_heures_sup' => ['sometimes', 'numeric', 'min:0', 'max:2'],
        ]);

        $employe = Employe::findOrFail($data['employe_id']);

        $paie = $this->paieCalculService->calculerPourEmploye(
            employe: $employe,
            mois: $data['mois'],
            annee: $data['annee'],
            primesInput: collect($data['primes'] ?? []),
            retenuesInput: collect($data['retenues'] ?? []),
            nombreHeuresSupplementaires: (float) ($data['nombre_heures_supplementaires'] ?? 0),
            tauxMajorationHeuresSup: (float) ($data['taux_majoration_heures_sup'] ?? 0.25),
        );

        return response()->json($paie);
    }

    /**
     * POST /api/paies/{paie}/valider
     * Verrouille définitivement la fiche de paie et génère le bulletin PDF via PdfGeneratorService.
     */
    public function valider(Paie $paie): JsonResponse
    {
        $this->authorize('valider', $paie);

        $paie->update(['statut' => Paie::STATUT_VALIDEE]);

        $cheminPdf = $this->pdfGeneratorService->genererBulletin($paie);

        $paie->bulletin()->updateOrCreate([], [
            'fichier_pdf' => $cheminPdf,
            'date_generation' => now(),
        ]);

        return response()->json($paie->fresh(['employe', 'bulletin']));
    }

    /**
     * GET /api/paies/{paie}/anomalies
     * Retourne les anomalies détectées sur cette fiche de paie (module d'assistance).
     */
    public function anomalies(Paie $paie, \App\Services\DetectionAnomalieService $anomalyDetectionService): JsonResponse
    {
        $this->authorize('view', $paie);

        return response()->json([
            'anomalies' => $anomalyDetectionService->detecterPourPaie($paie),
        ]);
    }

    /**
     * DELETE /api/paies/{paie}
     * Uniquement possible sur une paie en brouillon (voir PaiePolicy).
     */
    public function destroy(Paie $paie): JsonResponse
    {
        $this->authorize('delete', $paie);

        $paie->delete();

        return response()->json(['message' => 'Fiche de paie supprimée.']);
    }
}
