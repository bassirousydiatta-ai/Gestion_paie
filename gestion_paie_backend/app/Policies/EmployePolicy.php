<?php

namespace App\Policies;

use App\Models\Employe;
use App\Models\User;

class EmployePolicy
{
    /**
     * Admin et RH gèrent les employés. Le Comptable peut uniquement consulter
     * (il en a besoin pour établir les paies) mais ne peut ni créer, ni modifier, ni supprimer.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function view(User $user, Employe $employe): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh'], true);
    }

    public function update(User $user, Employe $employe): bool
    {
        return in_array($user->role, ['admin', 'rh'], true);
    }

    /**
     * La suppression définitive est réservée à l'Admin.
     * L'archivage (changement de statut) passe par update(), pas par delete().
     */
    public function delete(User $user, Employe $employe): bool
    {
        return $user->role === 'admin';
    }
}
