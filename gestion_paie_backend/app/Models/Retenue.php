<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retenue extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'paie_id',
        'libelle',
        'montant',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    /**
     * La fiche de paie concernée par cette retenue.
     */
    public function paie(): BelongsTo
    {
        return $this->belongsTo(Paie::class);
    }
}
