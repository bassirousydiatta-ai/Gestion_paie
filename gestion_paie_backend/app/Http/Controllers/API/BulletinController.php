<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bulletin_Paie;
use App\Models\Employe;
use App\Models\Paie;
use App\Services\PdfGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulletinController extends Controller
{
    public function __construct(
        private readonly PdfGeneratorService $pdfGeneratorService,
    ) {
    }

    /**
     * GET /api/employes/{employe}/bulletins
     * Historique des bulletins d'un employé.
     */
    public function index(Employe $employe): JsonResponse
    {
        $this->authorize('viewAny', Bulletin_Paie::class);

        $bulletins = Bulletin_Paie::query()
            ->whereHas('paie', fn ($q) => $q->where('employe_id', $employe->id))
            ->with('paie')
            ->latest('date_generation')
            ->get();

        return response()->json($bulletins);
    }

    /**
     * GET /api/bulletins/{bulletin}
     * Détail d'un bulletin (métadonnées, sans le contenu du PDF).
     */
    public function show(Bulletin_Paie $bulletin): JsonResponse
    {
        $this->authorize('view', $bulletin);

        return response()->json($bulletin->load('paie.employe'));
    }

    /**
     * POST /api/paies/{paie}/bulletin
     * (Re)génère manuellement le bulletin PDF d'une paie déjà validée.
     * En temps normal, la génération est déclenchée automatiquement par
     * PaieController::valider() ; cette route sert surtout à régénérer
     * un PDF manquant ou corrompu.
     */
    public function store(Paie $paie): JsonResponse
    {
        $this->authorize('create', Bulletin_Paie::class);

        if ($paie->statut !== 'validee') {
            return response()->json([
                'message' => 'Le bulletin ne peut être généré que pour une paie validée.',
            ], 409);
        }

        // Génération réelle via PdfGeneratorService.
        $cheminPdf = $this->pdfGeneratorService->genererBulletin($paie);

        $bulletin = $paie->bulletin()->updateOrCreate([], [
            'fichier_pdf' => $cheminPdf,
            'date_generation' => now(),
        ]);

        return response()->json($bulletin, 201);
    }

    /**
     * GET /api/bulletins/{bulletin}/pdf
     * Téléchargement direct du fichier PDF.
     */
    public function download(Bulletin_Paie $bulletin): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $bulletin);

        if (!Storage::disk('public')->exists($bulletin->fichier_pdf)) {
            return response()->json([
                'message' => 'Le fichier PDF de ce bulletin est introuvable.',
            ], 404);
        }

        return Storage::disk('public')->download(
            $bulletin->fichier_pdf,
            "bulletin_{$bulletin->paie->mois}_{$bulletin->paie->annee}.pdf"
        );
    }

    /**
     * DELETE /api/bulletins/{bulletin}
     * Réservé à l'Admin (voir BulletinPaiePolicy) — supprime le fichier et l'entrée en base.
     */
    public function destroy(Bulletin_Paie $bulletin): JsonResponse
    {
        $this->authorize('delete', $bulletin);

        Storage::disk('public')->delete($bulletin->fichier_pdf);
        $bulletin->delete();

        return response()->json(['message' => 'Bulletin supprimé.']);
    }
}
