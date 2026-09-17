<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Poste;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PosteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postesParDepartement = [
            'Ressources humaines' => ['Responsable RH', 'Assistant RH'],
            'Finance' => ['Comptable', 'Responsable financier'],
            'Informatique' => ['Developpeur', 'Administrateur systemes'],
        ];

        foreach ($postesParDepartement as $departementNom => $intitules) {
            $departement = Departement::where('nom', $departementNom)->first();

            foreach ($intitules as $intitule) {
                Poste::firstOrCreate([
                    'departement_id' => $departement->id,
                    'intitule' => $intitule,
                ]);
            }
        }
    }
}
