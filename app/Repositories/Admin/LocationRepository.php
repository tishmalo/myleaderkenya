<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\LocationRepositoryInterface;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getAllLocations(): Collection
    {
        return Location::all();
    }
}
