<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesCatalogAccess;

class DepartementPolicy
{
    use AuthorizesCatalogAccess;

    protected array $manageableBy = ['admin', 'rh'];
}
