<?php

namespace App\Contracts\Repositories\Api;

use App\Models\User;
use Illuminate\Support\Collection;

interface LocationRepositoryInterface
{
    public function getAllCounties(): Collection;

    public function getCountiesByBloc(int $blocId): Collection;

    public function getCountiesByName(string $name): Collection;

    public function getConstituenciesByCountyName(string $countyName): Collection;

    public function getWardsByConstituencyName(string $constituencyName): Collection;

    public function getPollingStationsByType(string $type, int $id): Collection;

    public function getPollingStationsByWardName(string $wardName): Collection;

    public function getTags(): Collection;

    public function getAllBlocs(): Collection;

    public function getAllLocations(): Collection;

    public function uploadLocation(User $user, float $latitude, float $longitude): void;
}
