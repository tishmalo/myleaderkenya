<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    public function getAllLocations(): Collection;
}
