<?php

namespace App\Policies;

use App\Models\Contrat;
use App\Models\User;

class ContratPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function view(User $user, Contrat $contrat): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh'], true);
    }

    public function update(User $user, Contrat $contrat): bool
    {
        return in_array($user->role, ['admin', 'rh'], true);
    }

    public function delete(User $user, Contrat $contrat): bool
    {
        return $user->role === 'admin';
    }
}
