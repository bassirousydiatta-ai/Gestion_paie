<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Services\DetectionAnomalieService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeController extends Controller
{
    public function __construct(
        private readonly PdfGeneratorService $pdfGeneratorService,
        private readonly DetectionAnomalieService $anomalyDetectionService,
    ) {
    }

    /**
     * GET /api/employes
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Employe::class);

        $employes = Employe::query()
            ->when($request->filled('recherche'), function ($query) use ($request) {
                $terme = $request->string('recherche');
                $query->where(function ($q) use ($terme) {
                    $q->where('nom', 'like', "%{$terme}%")
                        ->orWhere('prenom', 'like', "%{$terme}%")
                        ->orWhere('cin', 'like', "%{$terme}%");
                });
            })
            ->when($request->filled('statut'), fn ($query) => $query->where('statut', $request->string('statut')))
            ->with(['contrats' => fn ($q) => $q->latest('date_debut')->limit(1)])
            ->orderBy('nom')
            ->paginate($request->integer('par_page', 15));

        return response()->json($employes);
    }

    /**
     * POST /api/employes
     * La réponse inclut un champ "anomalies" (module d'assistance) — non bloquant,
     * purement informatif pour le front (ex. rappel de compléter le téléphone).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Employe::class);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'cin' => ['required', 'string', 'max:20', 'unique:employes,cin'],
            'date_naissance' => ['required', 'date', 'before:-18 years'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'date_embauche' => ['required', 'date'],
            'statut' => ['sometimes', Rule::in(['actif', 'archive'])],
        ]);

        $employe = Employe::create($data);

        return response()->json([
            'employe' => $employe,
            'anomalies' => $this->anomalyDetectionService->detecterChampsManquants($employe),
        ], 201);
    }

    /**
     * GET /api/employes/{employe}
     */
    public function show(Employe $employe): JsonResponse
    {
        $this->authorize('view', $employe);

        return response()->json(
            $employe->load(['contrats.poste.departement', 'user', 'paies' => fn ($q) => $q->latest()->limit(12)])
        );
    }

    /**
     * PUT/PATCH /api/employes/{employe}
     */
    public function update(Request $request, Employe $employe): JsonResponse
    {
        $this->authorize('update', $employe);

        $data = $request->validate([
            'nom' => ['sometimes', 'string', 'max:100'],
            'prenom' => ['sometimes', 'string', 'max:100'],
            'cin' => ['sometimes', 'string', 'max:20', Rule::unique('employes', 'cin')->ignore($employe->id)],
            'date_naissance' => ['sometimes', 'date', 'before:-18 years'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'date_embauche' => ['sometimes', 'date'],
            'statut' => ['sometimes', Rule::in(['actif', 'archive'])],
        ]);

        $employe->update($data);

        return response()->json($employe->fresh());
    }

    /**
     * DELETE /api/employes/{employe}
     */
    public function destroy(Employe $employe): JsonResponse
    {
        $this->authorize('delete', $employe);

        $employe->delete();

        return response()->json(['message' => 'Employé supprimé.']);
    }

    /**
     * PATCH /api/employes/{employe}/archiver
     */
    public function archiver(Employe $employe): JsonResponse
    {
        $this->authorize('update', $employe);

        $employe->update(['statut' => 'archive']);

        return response()->json($employe->fresh());
    }

    /**
     * GET /api/employes/{employe}/attestation-travail
     */
    public function attestationTravail(Employe $employe): StreamedResponse
    {
        $this->authorize('view', $employe);

        $chemin = $this->pdfGeneratorService->genererAttestationTravail($employe);

        return Storage::disk('public')->download(
            $chemin,
            "attestation_travail_{$employe->nom}_{$employe->prenom}.pdf"
        );

    }

    /**
     * GET /api/employes/{employe}/anomalies
     * Vérifie les champs obligatoires/recommandés manquants pour cet employé.
     */
    public function anomalies(Employe $employe): JsonResponse
    {
        $this->authorize('view', $employe);

        return response()->json([
            'anomalies' => $this->anomalyDetectionService->detecterChampsManquants($employe),
        ]);
    }
}
