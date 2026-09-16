<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bulletin_paie extends Model
{
    use HasFactory;

    protected $table = 'bulletin_paie';
    protected $primaryKey = 'id';
    protected $fillable = [
        'paie_id',
        'fichier_pdf',
        'date_generation',
    ];

    protected $casts = [
        'date_generation' => 'datetime',
    ];

    protected $appends = [
        'url_telechargement',
    ];

    /**
     * La fiche de paie correspondant à ce bulletin.
     */
    public function paie(): BelongsTo
    {
        return $this->belongsTo(Paie::class);
    }

    /**
     * URL publique de téléchargement du PDF (stockage local "public").
     */
    public function getUrlTelechargementAttribute(): string
    {
        return asset('storage/' . ltrim($this->fichier_pdf, '/'));
    }
}
