<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesCatalogAccess;

class PrimePolicy
{
    use AuthorizesCatalogAccess;

    protected array $manageableBy = ['admin', 'comptable'];
}
