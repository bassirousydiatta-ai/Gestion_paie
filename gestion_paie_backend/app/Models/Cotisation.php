<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cotisation extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'nom',
        'taux',
        'plafond',
    ];

    protected $casts = [
        'taux' => 'decimal:2',
        'plafond' => 'decimal:2',
    ];

    /**
     * Les fiches de paie auxquelles cette cotisation a été appliquée.
     */
    public function paies(): BelongsToMany
    {
        return $this->belongsToMany(Paie::class, 'paie_cotisation')
            ->withPivot('montant_calcule')
            ->withTimestamps();
    }

    /**
     * Calcule le montant de la cotisation pour un salaire brut donné,
     * en respectant le plafond éventuel.
     */
    public function calculerMontant(float $salaireBrut): float
    {
        $assiette = $this->plafond
            ? min($salaireBrut, (float) $this->plafond)
            : $salaireBrut;

        return round($assiette * ((float) $this->taux / 100), 2);
    }
}
