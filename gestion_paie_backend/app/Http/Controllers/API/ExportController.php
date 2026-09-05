<?php

namespace App\Http\Controllers\Api;

use App\Exports\EmployesExport;
use App\Exports\PaiesExport;
use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Paie;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * GET /api/exports/paies/xlsx?mois=&annee=&employe_id=
     */
    public function paiesExcel(Request $request): Response
    {
        $this->authorize('viewAny', Paie::class);

        $export = new PaiesExport(
            mois: $request->integer('mois') ?: null,
            annee: $request->integer('annee') ?: null,
            employeId: $request->integer('employe_id') ?: null,
        );

        return Excel::download($export, $this->nomFichier('paies', 'xlsx'));
    }

    /**
     * GET /api/exports/paies/pdf?mois=&annee=&employe_id=
     */
    public function paiesPdf(Request $request): Response
    {
        $this->authorize('viewAny', Paie::class);

        $mois = $request->integer('mois') ?: null;
        $annee = $request->integer('annee') ?: null;

        $paies = Paie::query()
            ->with('employe')
            ->when($mois, fn ($q) => $q->where('mois', $mois))
            ->when($annee, fn ($q) => $q->where('annee', $annee))
            ->when($request->filled('employe_id'), fn ($q) => $q->where('employe_id', $request->integer('employe_id')))
            ->orderBy('annee')->orderBy('mois')
            ->get();

        $periodeLabel = $mois && $annee
            ? \Carbon\Carbon::create()->month($mois)->translatedFormat('F') . " {$annee}"
            : ($annee ? "Année {$annee}" : 'Toutes périodes');

        $pdf = Pdf::loadView('pdf.paies-export', compact('paies', 'periodeLabel'))->setPaper('a4', 'landscape');

        return $pdf->download($this->nomFichier('paies', 'pdf'));
    }

    /**
     * GET /api/exports/employes/xlsx?statut=
     */
    public function employesExcel(Request $request): Response
    {
        $this->authorize('viewAny', Employe::class);

        $export = new EmployesExport(statut: $request->string('statut')->toString() ?: null);

        return Excel::download($export, $this->nomFichier('employes', 'xlsx'));
    }

    private function nomFichier(string $prefixe, string $extension): string
    {
        return "{$prefixe}_" . now()->format('Y-m-d_His') . ".{$extension}";
    }
}
