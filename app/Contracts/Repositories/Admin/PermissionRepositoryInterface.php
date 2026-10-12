<?php

namespace App\Contracts\Repositories\Admin;

use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    public function allGroupedByGroup(): Collection;
}
