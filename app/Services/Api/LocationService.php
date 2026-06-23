<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\LocationRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class LocationService
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository
    ) {}

    public function getAllCounties(): Collection
    {
        return $this->locationRepository->getAllCounties();
    }

    public function getCountiesByBloc(int $blocId): Collection
    {
        return $this->locationRepository->getCountiesByBloc($blocId);
    }

    public function getCountiesByName(string $name): Collection
    {
        return $this->locationRepository->getCountiesByName($name);
    }

    public function getConstituenciesByCountyName(?string $countyName): Collection
    {
        if (!$countyName) {
            return collect([]);
        }

        return $this->locationRepository->getConstituenciesByCountyName($countyName);
    }

    public function getWardsByConstituencyName(?string $constituencyName): Collection
    {
        if (!$constituencyName) {
            return collect([]);
        }

        return $this->locationRepository->getWardsByConstituencyName($constituencyName);
    }

    public function getPollingStationsByType(string $type, int $id): Collection
    {
        return $this->locationRepository->getPollingStationsByType($type, $id);
    }

    public function getPollingStationsByWardName(?string $wardName): Collection
    {
        if (!$wardName) {
            return collect([]);
        }

        return $this->locationRepository->getPollingStationsByWardName($wardName);
    }

    public function getTags(): Collection
    {
        return $this->locationRepository->getTags();
    }

    public function getAllLocations(): Collection
    {
        return $this->locationRepository->getAllLocations();
    }

    public function uploadLocation(User $user, float $latitude, float $longitude): void
    {
        $this->locationRepository->uploadLocation($user, $latitude, $longitude);
    }
}
