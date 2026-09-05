<?php

namespace App\Policies;

use App\Models\Paie;
use App\Models\User;

class PaiePolicy
{
    /**
     * Le RH peut consulter les paies (utile pour son tableau de bord)
     * mais seul le Comptable (et l'Admin) peut créer/modifier/valider.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function view(User $user, Paie $paie): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'comptable'], true);
    }

    public function update(User $user, Paie $paie): bool
    {
        // Une paie déjà validée ne peut plus être modifiée, sauf par l'Admin
        if ($paie->statut === 'validee' && $user->role !== 'admin') {
            return false;
        }

        return in_array($user->role, ['admin', 'comptable'], true);
    }

    public function delete(User $user, Paie $paie): bool
    {
        // On ne supprime jamais une paie validée (traçabilité comptable/légale)
        if ($paie->statut === 'validee') {
            return false;
        }

        return $user->role === 'admin';
    }

    /**
     * Ability personnalisée : valider définitivement une fiche de paie.
     * Utilisation : $this->authorize('valider', $paie);
     */
    public function valider(User $user, Paie $paie): bool
    {
        return in_array($user->role, ['admin', 'comptable'], true)
            && $paie->statut !== 'validee';
    }
}
