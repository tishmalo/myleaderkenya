<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Support\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function allGroupedByGroup(): Collection
    {
        return Permission::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');
    }
}
