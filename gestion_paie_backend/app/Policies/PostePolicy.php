<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesCatalogAccess;

class PostePolicy
{
    use AuthorizesCatalogAccess;

    protected array $manageableBy = ['admin', 'rh'];
}
