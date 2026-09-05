<?php

namespace App\Exports;

use App\Models\Employe;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?string $statut = null,
    ) {
    }

    public function query(): Builder
    {
        return Employe::query()
            ->with(['contrats' => fn ($q) => $q->latest('date_debut')->limit(1), 'contrats.poste.departement'])
            ->when($this->statut, fn ($q) => $q->where('statut', $this->statut))
            ->orderBy('nom');
    }

    public function headings(): array
    {
        return [
            'Nom', 'Prénom', 'CIN', 'Date de naissance', 'Téléphone',
            'Date d\'embauche', 'Statut', 'Poste', 'Département', 'Salaire de base',
        ];
    }

    public function map($employe): array
    {
        $contrat = $employe->contrats->first();

        return [
            $employe->nom,
            $employe->prenom,
            $employe->cin,
            optional($employe->date_naissance)->format('d/m/Y'),
            $employe->telephone,
            optional($employe->date_embauche)->format('d/m/Y'),
            ucfirst($employe->statut),
            $contrat?->poste?->intitule ?? '—',
            $contrat?->poste?->departement?->nom ?? '—',
            $contrat ? (float) $contrat->salaire_base : null,
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
