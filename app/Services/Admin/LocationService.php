<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository
    ) {}

    public function getAllLocations(): Collection
    {
        return $this->locationRepository->getAllLocations();
    }
}
