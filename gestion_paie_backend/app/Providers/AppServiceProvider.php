<?php

namespace App\Providers;

use App\Models\Bulletin_Paie;
use App\Models\Cotisation;
use App\Models\Contrat;
use App\Models\Departement;
use App\Models\Employe;
use App\Models\Paie;
use App\Models\Poste;
use App\Models\Prime;
use App\Models\User;
use App\Policies\BulletinPaiePolicy;
use App\Policies\CotisationPolicy;
use App\Policies\ContratPolicy;
use App\Policies\DepartementPolicy;
use App\Policies\EmployePolicy;
use App\Policies\PaiePolicy;
use App\Policies\PostePolicy;
use App\Policies\PrimePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Correspondance Modèle => Policy.
     *
     * NOTE : depuis Laravel 11, si vos policies suivent la convention de
     * nommage standard (App\Models\Employe => App\Policies\EmployePolicy),
     * cette déclaration explicite n'est plus strictement obligatoire
     * (auto-découverte). On la garde ici de façon explicite pour la clarté
     * et pour rester compatible avec toutes les versions de Laravel.
     */
    protected $policies = [
        Employe::class => EmployePolicy::class,
        Contrat::class => ContratPolicy::class,
        Paie::class => PaiePolicy::class,
        User::class => UserPolicy::class,
        Departement::class => DepartementPolicy::class,
        Poste::class => PostePolicy::class,
        Prime::class => PrimePolicy::class,
        Cotisation::class => CotisationPolicy::class,
        Bulletin_Paie::class => BulletinPaiePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
