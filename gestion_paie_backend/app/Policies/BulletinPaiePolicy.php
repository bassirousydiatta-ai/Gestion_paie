<?php

namespace App\Policies;

use App\Models\Bulletin_Paie;
use App\Models\User;

class BulletinPaiePolicy
{
    /**
     * Un bulletin suit les mêmes règles d'accès que la paie dont il découle :
     * tout le monde (Admin, RH, Comptable) peut consulter/télécharger,
     * mais seul le Comptable (et l'Admin) peut en déclencher la génération
     * ou le supprimer — la génération est en pratique automatique lors de la
     * validation d'une paie (Paie::valider), mais on garde ces règles pour
     * une génération/suppression manuelle éventuelle (ex. régénérer un PDF corrompu).
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function view(User $user, Bulletin_Paie $bulletin): bool
    {
        return in_array($user->role, ['admin', 'rh', 'comptable'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'comptable'], true);
    }

    public function update(User $user, Bulletin_Paie $bulletin): bool
    {
        return in_array($user->role, ['admin', 'comptable'], true);
    }

    public function delete(User $user, Bulletin_Paie $bulletin): bool
    {
        return $user->role === 'admin';
    }
}
