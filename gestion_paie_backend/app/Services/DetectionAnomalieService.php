<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Paie;
use Illuminate\Support\Collection;

/**
 * Module d'assistance "intelligent" au sens de la fiche de stage : il ne s'agit
 * pas d'un modèle de machine learning, mais d'un moteur de règles qui détecte
 * des incohérences courantes avant la validation d'une paie ou la sauvegarde
 * d'un employé. C'est volontairement simple, transparent et sans dépendance
 * externe — chaque règle est une méthode indépendante, facile à activer/désactiver
 * ou à affiner.
 */
class DetectionAnomalieService
{
    /**
     * Seuil (en écarts-types) au-delà duquel un salaire net est jugé "anormal"
     * par rapport à la moyenne des autres employés occupant le même poste.
     */
    private const SEUIL_ECART_TYPE_SALAIRE = 1.5;

    /**
     * Nombre d'heures supplémentaires mensuelles au-delà duquel une alerte
     * est levée (le Code du travail marocain plafonne à 10h/semaine, soit ~40h/mois).
     */
    private const SEUIL_HEURES_SUP_ELEVE = 40;

    /**
     * Analyse une fiche de paie et retourne la liste des anomalies détectées.
     *
     * @return array<int, array{code: string, gravite: string, message: string, suggestion: ?string}>
     */
    public function detecterPourPaie(Paie $paie): array
    {
        $paie->loadMissing(['employe', 'primes', 'retenues']);

        $anomalies = collect([
            $this->verifierSalaireNetNegatifOuNul($paie),
            $this->verifierSalaireAnormal($paie),
            $this->verifierHeuresSupplementairesExcessives($paie),
            $this->verifierRetenuesElevees($paie),
            $this->verifierAbsenceDePrimeRecurrente($paie),
        ])->flatten(1)->filter()->values();

        return $anomalies
            ->merge($this->detecterChampsManquants($paie->employe))
            ->values()
            ->toArray();
    }

    /**
     * Vérifie les champs obligatoires/recommandés d'un employé, indépendamment
     * de toute paie (utile aussi à l'enregistrement d'un nouvel employé).
     *
     * @return array<int, array{code: string, gravite: string, message: string, suggestion: ?string}>
     */
    public function detecterChampsManquants(Employe $employe): array
    {
        $anomalies = [];

        if (blank($employe->adresse)) {
            $anomalies[] = $this->anomalie(
                'employe.adresse_manquante',
                'info',
                "L'adresse de {$employe->prenom} {$employe->nom} n'est pas renseignée.",
                "Complétez l'adresse pour la génération correcte des documents administratifs."
            );
        }

        if (blank($employe->telephone)) {
            $anomalies[] = $this->anomalie(
                'employe.telephone_manquant',
                'info',
                "Le téléphone de {$employe->prenom} {$employe->nom} n'est pas renseigné.",
                null
            );
        }

        if (!$employe->contrats()->exists()) {
            $anomalies[] = $this->anomalie(
                'employe.aucun_contrat',
                'critique',
                "{$employe->prenom} {$employe->nom} n'a aucun contrat enregistré.",
                'Créez un contrat avant de calculer sa paie.'
            );
        }

        return $anomalies;
    }

    /**
     * Un salaire net négatif ou nul est toujours une erreur de saisie/calcul.
     */
    private function verifierSalaireNetNegatifOuNul(Paie $paie): ?array
    {
        if ((float) $paie->salaire_net <= 0) {
            return $this->anomalie(
                'paie.salaire_net_invalide',
                'critique',
                "Le salaire net calculé est nul ou négatif ({$paie->salaire_net} MAD).",
                'Vérifiez les retenues et cotisations appliquées : elles dépassent probablement le salaire brut.'
            );
        }

        return null;
    }

    /**
     * Compare le salaire net de l'employé à la moyenne des autres employés
     * occupant le même poste, pour repérer un montant anormalement haut ou bas.
     */
    private function verifierSalaireAnormal(Paie $paie): ?array
    {
        $contrat = $paie->employe?->contrats()->latest('date_debut')->first();
        if (!$contrat) {
            return null;
        }

        $salairesComparables = Paie::query()
            ->whereHas('employe.contrats', fn ($q) => $q->where('poste_id', $contrat->poste_id))
            ->where('employe_id', '!=', $paie->employe_id)
            ->pluck('salaire_net')
            ->map(fn ($v) => (float) $v);

        if ($salairesComparables->count() < 3) {
            return null; // Pas assez de données pour une comparaison fiable
        }

        $moyenne = $salairesComparables->avg();
        $ecartType = $this->ecartType($salairesComparables, $moyenne);

        if ($ecartType <= 0) {
            return null;
        }

        $ecartActuel = abs((float) $paie->salaire_net - $moyenne) / $ecartType;

        if ($ecartActuel >= self::SEUIL_ECART_TYPE_SALAIRE) {
            $sens = $paie->salaire_net > $moyenne ? 'plus élevé' : 'plus faible';

            return $this->anomalie(
                'paie.salaire_atypique',
                'avertissement',
                "Le salaire net ({$paie->salaire_net} MAD) est nettement {$sens} que la moyenne des employés au même poste (~" . round($moyenne, 2) . ' MAD).',
                'Vérifiez que le salaire de base, les primes et les retenues ont été correctement saisis.'
            );
        }

        return null;
    }

    private function verifierHeuresSupplementairesExcessives(Paie $paie): ?array
    {
        if ((float) $paie->nombre_heures_supplementaires > self::SEUIL_HEURES_SUP_ELEVE) {
            return $this->anomalie(
                'paie.heures_sup_excessives',
                'avertissement',
                "{$paie->nombre_heures_supplementaires}h supplémentaires déclarées ce mois-ci, un volume inhabituellement élevé.",
                'Confirmez ce volume avec le service RH avant de valider la paie.'
            );
        }

        return null;
    }

    private function verifierRetenuesElevees(Paie $paie): ?array
    {
        $brut = (float) $paie->salaire_brut;
        $retenues = (float) $paie->total_retenues;

        if ($brut > 0 && $retenues > 0 && ($retenues / $brut) > 0.15) {
            return $this->anomalie(
                'paie.retenues_elevees',
                'avertissement',
                'Les retenues représentent plus de 15% du salaire brut (' . round(($retenues / $brut) * 100, 1) . '%).',
                'Vérifiez le détail des retenues appliquées avant de valider.'
            );
        }

        return null;
    }

    /**
     * Repère un employé qui recevait habituellement une prime (transport, etc.)
     * mais dont la paie du mois n'en contient aucune — signe possible d'un oubli.
     */
    private function verifierAbsenceDePrimeRecurrente(Paie $paie): ?array
    {
        if ($paie->primes->isNotEmpty()) {
            return null;
        }

        $primeHabituelle = Paie::query()
            ->where('employe_id', $paie->employe_id)
            ->where('id', '!=', $paie->id)
            ->whereHas('primes')
            ->exists();

        if ($primeHabituelle) {
            return $this->anomalie(
                'paie.prime_habituelle_absente',
                'info',
                'Cet employé recevait généralement au moins une prime les mois précédents, mais aucune prime n\'est appliquée ce mois-ci.',
                "Vérifiez qu'il ne s'agit pas d'un oubli avant de valider."
            );
        }

        return null;
    }

    private function ecartType(Collection $valeurs, float $moyenne): float
    {
        if ($valeurs->count() < 2) {
            return 0.0;
        }

        $sommeCarres = $valeurs->reduce(fn ($carry, $v) => $carry + ($v - $moyenne) ** 2, 0.0);

        return sqrt($sommeCarres / $valeurs->count());
    }

    private function anomalie(string $code, string $gravite, string $message, ?string $suggestion): array
    {
        return [
            'code' => $code,
            'gravite' => $gravite, // 'info' | 'avertissement' | 'critique'
            'message' => $message,
            'suggestion' => $suggestion,
        ];
    }
}
