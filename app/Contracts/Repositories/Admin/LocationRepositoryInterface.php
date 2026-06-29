<?php

namespace App\Contracts\Repositories\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    public function getAllLocations(): Collection;

    public function getPaginatedLocations(int $perPage = 50): LengthAwarePaginator;
}
