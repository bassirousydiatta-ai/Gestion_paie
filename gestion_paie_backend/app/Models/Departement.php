<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'nom',
    ];

    /**
     * Les postes rattachés à ce département.
     */
    public function postes(): HasMany
    {
        return $this->hasMany(Poste::class);
    }
}
