<?php

namespace App\Services;

use App\Models\Cotisation;
use App\Models\Employe;
use App\Models\Paie;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaieCalculService
{
    /**
     * Durée légale mensuelle de travail, utilisée comme base pour calculer
     * le taux horaire à partir du salaire de base (191h/mois est la durée
     * légale marocaine standard : 44h/semaine × 52 semaines / 12 mois).
     * Adaptez cette constante à la réglementation de votre pays si besoin.
     */
    private const HEURES_MENSUELLES_LEGALES = 191;

    /**
     * Barème mensuel de l'IR (Impôt sur le Revenu), à titre d'exemple pour le Maroc.
     * Chaque tranche : [borne_min, borne_max (null = infini), taux, somme_a_deduire].
     *
     * ⚠️ Adaptez ces tranches à la réglementation en vigueur dans votre pays / année fiscale.
     */
    private const BAREME_IR_MENSUEL = [
        ['min' => 0,      'max' => 3000,  'taux' => 0.00, 'deduction' => 0],
        ['min' => 3000,   'max' => 5000,  'taux' => 0.10, 'deduction' => 300],
        ['min' => 5000,   'max' => 6000,  'taux' => 0.20, 'deduction' => 800],
        ['min' => 6000,   'max' => 8000,  'taux' => 0.30, 'deduction' => 1400],
        ['min' => 8000,   'max' => 30000, 'taux' => 0.34, 'deduction' => 1720],
        ['min' => 30000,  'max' => null,  'taux' => 0.38, 'deduction' => 2920],
    ];

    /**
     * Calcule (ou recalcule) la fiche de paie d'un employé pour une période donnée.
     *
     * @param Employe $employe
     * @param int $mois
     * @param int $annee
     * @param Collection<int, array{prime_id:int, montant_applique:float}> $primesInput
     * @param Collection<int, array{libelle:string, montant:float}> $retenuesInput
     * @param float $nombreHeuresSupplementaires Nombre d'heures sup. effectuées sur la période
     * @param float $tauxMajorationHeuresSup Majoration applicable (0.25 = +25%, 0.50 = +50%, 1.00 = +100%)
     *
     * @throws ValidationException si l'employé n'a pas de contrat, ou si la paie est déjà validée
     */
    public function calculerPourEmploye(
        Employe $employe,
        int $mois,
        int $annee,
        Collection $primesInput,
        Collection $retenuesInput,
        float $nombreHeuresSupplementaires = 0,
        float $tauxMajorationHeuresSup = 0.25
    ): Paie {
        $contrat = $employe->contrats()->latest('date_debut')->first();

        if (!$contrat) {
            throw ValidationException::withMessages([
                'employe_id' => ["Cet employé n'a aucun contrat : impossible de calculer sa paie."],
            ]);
        }

        return DB::transaction(function () use (
            $employe, $mois, $annee, $primesInput, $retenuesInput,
            $nombreHeuresSupplementaires, $tauxMajorationHeuresSup, $contrat
        ) {
            $paie = Paie::firstOrNew([
                'employe_id' => $employe->id,
                'mois' => $mois,
                'annee' => $annee,
            ]);

            if ($paie->exists && $paie->statut === Paie::STATUT_VALIDEE) {
                throw ValidationException::withMessages([
                    'statut' => ['Cette fiche de paie est déjà validée et ne peut plus être recalculée.'],
                ]);
            }

            $salaireBase = (float) $contrat->salaire_base;

            $montantHeuresSup = $this->calculerMontantHeuresSupplementaires(
                $salaireBase,
                $nombreHeuresSupplementaires,
                $tauxMajorationHeuresSup
            );

            $totalPrimes = $primesInput->sum('montant_applique');
            $salaireBrut = round($salaireBase + $totalPrimes + $montantHeuresSup, 2);

            [$cotisationsData, $totalCotisations] = $this->calculerCotisations($salaireBrut);

            $assietteImposable = max($salaireBrut - $totalCotisations, 0);
            $impotRevenu = $this->calculerImpotRevenu($assietteImposable);

            $totalRetenues = round($retenuesInput->sum('montant'), 2);

            $salaireNet = round($salaireBrut - $totalCotisations - $impotRevenu - $totalRetenues, 2);

            $paie->fill([
                'salaire_brut' => $salaireBrut,
                'nombre_heures_supplementaires' => $nombreHeuresSupplementaires,
                'taux_majoration_heures_sup' => $tauxMajorationHeuresSup,
                'montant_heures_supplementaires' => $montantHeuresSup,
                'total_cotisations' => round($totalCotisations, 2),
                'total_retenues' => $totalRetenues,
                'impot_revenu' => $impotRevenu,
                'salaire_net' => $salaireNet,
                'statut' => Paie::STATUT_BROUILLON,
            ])->save();

            $paie->primes()->sync(
                $primesInput->keyBy('prime_id')
                    ->map(fn ($p) => ['montant_applique' => $p['montant_applique']])
                    ->toArray()
            );
            $paie->cotisations()->sync($cotisationsData);

            $paie->retenues()->delete();
            foreach ($retenuesInput as $retenue) {
                $paie->retenues()->create($retenue);
            }

            return $paie->fresh(['employe', 'primes', 'cotisations', 'retenues']);
        });
    }

    /**
     * Calcule le montant des heures supplémentaires.
     * Formule : taux horaire (= salaire de base / durée légale mensuelle) × heures × (1 + majoration)
     */
    public function calculerMontantHeuresSupplementaires(
        float $salaireBase,
        float $nombreHeures,
        float $tauxMajoration
    ): float {
        if ($nombreHeures <= 0) {
            return 0.0;
        }

        $tauxHoraire = $salaireBase / self::HEURES_MENSUELLES_LEGALES;

        return round($tauxHoraire * $nombreHeures * (1 + $tauxMajoration), 2);
    }

    /**
     * Applique l'intégralité du catalogue de cotisations au salaire brut.
     *
     * @return array{0: array<int, array{montant_calcule: float}>, 1: float}
     */
    private function calculerCotisations(float $salaireBrut): array
    {
        $cotisationsData = [];
        $total = 0.0;

        foreach (Cotisation::all() as $cotisation) {
            $montant = $cotisation->calculerMontant($salaireBrut);
            $cotisationsData[$cotisation->id] = ['montant_calcule' => $montant];
            $total += $montant;
        }

        return [$cotisationsData, $total];
    }

    /**
     * Calcule l'IR selon le barème progressif par tranches.
     */
    public function calculerImpotRevenu(float $assietteImposable): float
    {
        if ($assietteImposable <= 0) {
            return 0.0;
        }

        foreach (self::BAREME_IR_MENSUEL as $tranche) {
            $dansLaTranche = $assietteImposable > $tranche['min']
                && ($tranche['max'] === null || $assietteImposable <= $tranche['max']);

            if ($dansLaTranche) {
                $impot = ($assietteImposable * $tranche['taux']) - $tranche['deduction'];

                return round(max($impot, 0), 2);
            }
        }

        return 0.0;
    }
}
