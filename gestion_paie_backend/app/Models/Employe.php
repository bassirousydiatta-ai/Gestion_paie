<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employe extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'nom',
        'prenom',
        'cin',
        'date_naissance',
        'adresse',
        'telephone',
        'date_embauche',
        'statut',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_embauche' => 'date',
    ];

    /**
     * Le compte utilisateur lié à cet employé (s'il existe).
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Les contrats de cet employé.
     */
    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    /**
     * Le contrat actuellement en cours (sans date de fin ou date de fin future).
     */
    public function contratActif(): HasOne
    {
        return $this->hasOne(Contrat::class)
            ->whereNull('date_fin')
            ->latestOfMany('date_debut');
    }

    /**
     * Les fiches de paie de cet employé.
     */
    public function paies(): HasMany
    {
        return $this->hasMany(Paie::class);
    }

    /**
     * Accesseur pratique : nom complet.
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Scope : uniquement les employés actifs.
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }
}
