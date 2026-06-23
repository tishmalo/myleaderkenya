<?php

namespace App\Contracts\Repositories\Api;

use App\Models\PollingStation;
use Illuminate\Support\Collection;

interface PollingStationRepositoryInterface
{
    public function create(array $data): PollingStation;

    public function getNearby(float $lat, float $lon, int $radius = 10000): Collection;

    public function getByCounty(string $county): Collection;

    public function import(array $stations): int;

    public function all(): Collection;

    public function latest(): Collection;

    public function getFiltered(string $type, string $id): Collection;
}
