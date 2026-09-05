<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrat extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'employe_id',
        'poste_id',
        'type_contrat',
        'salaire_base',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'salaire_base' => 'decimal:2',
    ];

    public const TYPE_CDI = 'CDI';
    public const TYPE_CDD = 'CDD';
    public const TYPE_STAGE = 'Stage';
    public const TYPE_INTERIM = 'Interim';

    /**
     * L'employé titulaire de ce contrat.
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * Le poste concerné par ce contrat.
     */
    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    /**
     * Indique si le contrat est actuellement actif.
     */
    public function getEstActifAttribute(): bool
    {
        return is_null($this->date_fin) || $this->date_fin->isFuture();
    }
}
