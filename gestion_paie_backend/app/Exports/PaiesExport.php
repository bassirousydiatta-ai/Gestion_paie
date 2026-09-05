<?php

namespace App\Exports;

use App\Models\Paie;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaiesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $mois = null,
        private readonly ?int $annee = null,
        private readonly ?int $employeId = null,
    ) {
    }

    public function query(): Builder
    {
        return Paie::query()
            ->with('employe')
            ->when($this->mois, fn ($q) => $q->where('mois', $this->mois))
            ->when($this->annee, fn ($q) => $q->where('annee', $this->annee))
            ->when($this->employeId, fn ($q) => $q->where('employe_id', $this->employeId))
            ->orderBy('annee')
            ->orderBy('mois');
    }

    public function headings(): array
    {
        return [
            'Employé', 'CIN', 'Mois', 'Année',
            'Salaire brut', 'Heures sup.', 'Cotisations', 'IR', 'Retenues',
            'Salaire net', 'Statut',
        ];
    }

    public function map($paie): array
    {
        return [
            "{$paie->employe->prenom} {$paie->employe->nom}",
            $paie->employe->cin,
            $paie->mois,
            $paie->annee,
            (float) $paie->salaire_brut,
            (float) $paie->nombre_heures_supplementaires,
            (float) $paie->total_cotisations,
            (float) $paie->impot_revenu,
            (float) $paie->total_retenues,
            (float) $paie->salaire_net,
            ucfirst($paie->statut),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'],
            ]],
        ];
    }
}
