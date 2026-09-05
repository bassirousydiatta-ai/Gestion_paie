<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Poste extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'departement_id',
        'intitule',
    ];

    /**
     * Le département auquel appartient ce poste.
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    /**
     * Les contrats associés à ce poste.
     */
    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }
}
