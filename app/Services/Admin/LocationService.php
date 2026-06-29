<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\LocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function getPaginatedLocations(int $perPage = 50): LengthAwarePaginator
    {
        return $this->locationRepository->getPaginatedLocations($perPage);
    }
}
