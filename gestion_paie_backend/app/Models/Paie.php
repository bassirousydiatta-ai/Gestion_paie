<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paie extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'mois',
        'annee',
        'salaire_brut',
        'nombre_heures_supplementaires',
        'taux_majoration_heures_sup',
        'montant_heures_supplementaires',
        'total_cotisations',
        'total_retenues',
        'impot_revenu',
        'salaire_net',
        'statut',
    ];

    protected $casts = [
        'salaire_brut' => 'decimal:2',
        'nombre_heures_supplementaires' => 'decimal:2',
        'taux_majoration_heures_sup' => 'decimal:2',
        'montant_heures_supplementaires' => 'decimal:2',
        'total_cotisations' => 'decimal:2',
        'total_retenues' => 'decimal:2',
        'impot_revenu' => 'decimal:2',
        'salaire_net' => 'decimal:2',
    ];

    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDEE = 'validee';

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function primes(): BelongsToMany
    {
        return $this->belongsToMany(Prime::class, 'paie_prime')
            ->withPivot('montant_applique')
            ->withTimestamps();
    }

    public function cotisations(): BelongsToMany
    {
        return $this->belongsToMany(Cotisation::class, 'paie_cotisation')
            ->withPivot('montant_calcule')
            ->withTimestamps();
    }

    public function retenues(): HasMany
    {
        return $this->hasMany(Retenue::class);
    }

    public function bulletin(): HasOne
    {
        return $this->hasOne(Bulletin_Paie::class);
    }

    public function scopeValidees($query)
    {
        return $query->where('statut', self::STATUT_VALIDEE);
    }

    public function scopePeriode($query, int $mois, int $annee)
    {
        return $query->where('mois', $mois)->where('annee', $annee);
    }
}
