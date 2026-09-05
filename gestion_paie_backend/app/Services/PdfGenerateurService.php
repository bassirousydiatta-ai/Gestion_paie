<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Paie;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorService
{
    /**
     * Génère le PDF du bulletin de paie et l'enregistre sur le disque "public".
     * Ne crée PAS l'enregistrement BulletinPaie en base — cela reste la
     * responsabilité de l'appelant (PaieController::valider(), par exemple),
     * afin que ce service reste concentré sur la seule génération du fichier.
     *
     * @return string Le chemin relatif du fichier sur le disque "public"
     */
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

    /**
     * Génère une attestation de travail pour un employé.
     *
     * @return string Le chemin relatif du fichier sur le disque "public"
     */
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
