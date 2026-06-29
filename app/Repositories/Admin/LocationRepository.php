<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\LocationRepositoryInterface;
use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getAllLocations(): Collection
    {
        return Location::query()->latest()->get();
    }

    public function getPaginatedLocations(int $perPage = 50): LengthAwarePaginator
    {
        return Location::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
