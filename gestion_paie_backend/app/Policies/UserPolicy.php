<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * La gestion des comptes utilisateurs est strictement réservée à l'Admin.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Un admin ne peut pas se supprimer lui-même (évite de perdre l'accès admin)
        return $user->role === 'admin' && $user->id !== $model->id;
    }
}
