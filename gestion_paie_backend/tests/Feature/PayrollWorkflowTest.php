<?php

namespace Tests\Feature;

use App\Models\Contrat;
use App\Models\Cotisation;
use App\Models\Departement;
use App\Models\Employe;
use App\Models\Poste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_login(): void
    {
        $registration = $this->postJson('/api/register', [
            'name' => 'Utilisateur Test',
            'email' => 'utilisateur@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $registration
            ->assertCreated()
            ->assertJsonPath('user.email', 'utilisateur@example.com')
            ->assertJsonPath('user.role', User::ROLE_RH)
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'utilisateur@example.com',
            'role' => User::ROLE_RH,
        ]);

        $this->postJson('/api/login', [
            'email' => 'utilisateur@example.com',
            'password' => 'Password123!',
        ])
            ->assertOk()
            ->assertJsonStructure(['user', 'token']);

        $this->postJson('/api/register', [
            'name' => 'Comptable Test',
            'email' => 'comptable@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => User::ROLE_COMPTABLE,
        ])
            ->assertCreated()
            ->assertJsonPath('user.role', User::ROLE_COMPTABLE);
    }

    public function test_an_authenticated_user_can_list_postes_and_create_a_contract(): void
    {
        $user = User::create([
            'name' => 'Responsable RH',
            'email' => 'rh@example.com',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_RH,
        ]);
        $departement = Departement::create(['nom' => 'Finance']);
        $poste = Poste::create([
            'departement_id' => $departement->id,
            'intitule' => 'Comptable',
        ]);
        $employe = Employe::create([
            'nom' => 'Diop',
            'prenom' => 'Awa',
            'email' => 'awa.diop@example.com',
            'cin' => 'TEST-001',
            'date_naissance' => '1995-01-01',
            'date_embauche' => '2026-01-01',
            'statut' => 'archive',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/postes')
            ->assertOk()
            ->assertJsonFragment(['intitule' => 'Comptable']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/contrats', [
                'employe_id' => $employe->id,
                'poste_id' => $poste->id,
                'type_contrat' => 'CDI',
                'salaire_base' => 7500,
                'date_debut' => '2026-01-01',
            ])
            ->assertCreated()
            ->assertJsonPath('poste.intitule', 'Comptable');

        $this->assertDatabaseHas('contrats', [
            'employe_id' => $employe->id,
            'poste_id' => $poste->id,
            'type_contrat' => 'CDI',
        ]);
    }

    public function test_an_admin_can_calculate_a_payroll_for_an_employee_with_contract(): void
    {
        $user = User::create([
            'name' => 'Admin Comptable',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_ADMIN,
        ]);

        $departement = Departement::create(['nom' => 'Finance']);
        $poste = Poste::create([
            'departement_id' => $departement->id,
            'intitule' => 'Comptable',
        ]);

        $employe = Employe::create([
            'nom' => 'Martin',
            'prenom' => 'Sarah',
            'email' => 'sarah.martin@example.com',
            'cin' => 'TEST-002',
            'date_naissance' => '1992-05-19',
            'date_embauche' => '2024-01-01',
            'statut' => 'actif',
        ]);

        Contrat::create([
            'employe_id' => $employe->id,
            'poste_id' => $poste->id,
            'type_contrat' => 'CDI',
            'salaire_base' => 10000,
            'date_debut' => '2024-01-01',
        ]);

        Cotisation::create([
            'nom' => 'CNSS',
            'taux' => 4.5,
            'plafond' => 5000,
        ]);

        Cotisation::create([
            'nom' => 'AM',
            'taux' => 2.5,
            'plafond' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/paies/calculer', [
                'employe_id' => $employe->id,
                'mois' => 9,
                'annee' => 2026,
                'primes' => [],
                'retenues' => [],
                'nombre_heures_supplementaires' => 0,
                'taux_majoration_heures_sup' => 0.25,
            ])
            ->assertOk()
            ->assertJsonPath('employe.id', $employe->id)
            ->assertJsonPath('statut', 'brouillon');

        $this->assertDatabaseHas('paies', [
            'employe_id' => $employe->id,
            'mois' => 9,
            'annee' => 2026,
        ]);
    }
}
