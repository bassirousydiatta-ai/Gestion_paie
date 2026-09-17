<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Ressources humaines', 'Finance', 'Informatique'] as $nom) {
            Departement::firstOrCreate(['nom' => $nom]);
        }
    }
}
