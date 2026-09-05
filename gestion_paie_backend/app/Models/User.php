<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'id';
    protected $fillable = [
        'employe_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public const ROLE_ADMIN = 'admin';
    public const ROLE_RH = 'rh';
    public const ROLE_COMPTABLE = 'comptable';

    /**
     * L'employé lié à ce compte utilisateur (s'il existe).
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * Vérifie si l'utilisateur possède un rôle donné.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isRh(): bool
    {
        return $this->hasRole(self::ROLE_RH);
    }

    public function isComptable(): bool
    {
        return $this->hasRole(self::ROLE_COMPTABLE);
    }
}
