<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prime extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'libelle',
        'montant',
        'type',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public const TYPE_FIXE = 'fixe';
    public const TYPE_POURCENTAGE = 'pourcentage';

    /**
     * Les fiches de paie auxquelles cette prime a été appliquée.
     */
    public function paies(): BelongsToMany
    {
        return $this->belongsToMany(Paie::class, 'paie_prime')
            ->withPivot('montant_applique')
            ->withTimestamps();
    }
}
