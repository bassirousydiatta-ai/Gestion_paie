<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Règle commune aux données de "catalogue" (départements, postes, primes, cotisations) :
 * - Admin et RH gèrent les départements/postes
 * - Admin et Comptable gèrent les primes/cotisations
 * - Tout utilisateur authentifié peut les consulter (nécessaire pour les listes déroulantes
 *   dans les formulaires employés/paies)
 *
 * Chaque policy qui utilise ce trait définit simplement $manageableBy.
 */
trait AuthorizesCatalogAccess
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, $this->manageableBy, true);
    }

    public function update(User $user, $model): bool
    {
        return in_array($user->role, $this->manageableBy, true);
    }

    public function delete(User $user, $model): bool
    {
        return $user->role === 'admin';
    }
}
