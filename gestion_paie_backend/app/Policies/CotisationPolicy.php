<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesCatalogAccess;

class CotisationPolicy
{
    use AuthorizesCatalogAccess;

    protected array $manageableBy = ['admin', 'comptable'];
}
