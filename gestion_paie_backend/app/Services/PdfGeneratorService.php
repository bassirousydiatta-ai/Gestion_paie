<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Paie;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorService
{
    public function genererBulletin(Paie $paie): string
    {
        $paie->loadMissing(['employe', 'primes', 'cotisations', 'retenues']);

        $pdf = Pdf::loadView('pdf.bulletin', [
            'paie' => $paie,
            'employe' => $paie->employe,
        ])->setPaper('a4');

        $chemin = "bulletins/{$paie->employe_id}_{$paie->annee}_{$paie->mois}.pdf";

        Storage::disk('public')->put($chemin, $pdf->output());

        return $chemin;
    }

    public function genererAttestationTravail(Employe $employe): string
    {
        $pdf = Pdf::loadView('pdf.attestation-travail', [
            'employe' => $employe,
            'contrat' => $employe->contrats()->latest('date_debut')->first(),
            'dateEdition' => now(),
        ])->setPaper('a4');

        $chemin = 'attestations/' . Str::slug("{$employe->nom}-{$employe->prenom}") . '-' . now()->timestamp . '.pdf';

        Storage::disk('public')->put($chemin, $pdf->output());

        return $chemin;
    }
}
