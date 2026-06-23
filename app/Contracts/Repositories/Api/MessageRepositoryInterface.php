<?php

namespace App\Contracts\Repositories\Api;

use App\Models\Message;
use Illuminate\Support\Collection;

interface MessageRepositoryInterface
{
    public function create(array $data): Message;

    public function getConstituencyMessages(string $county, string $constituency): Collection;

    public function getNearbyMessages(float $latitude, float $longitude, int $radius = 500): Collection;

    public function getLocationMessages(string $level, string $name, int $limit = 100): Collection;

    public function findById(int $id): ?Message;

    public function latest(int $limit = 50): Collection;

    public function count(): int;

    public function delete(Message $message): bool;
}
