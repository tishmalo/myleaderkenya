<?php

namespace App\Contracts\Repositories\Api;

interface StatsRepositoryInterface
{
    public function getTotalUsers(): int;

    public function getConfirmedVoters(): int;

    public function getTotalMessages(): int;

    public function getStationsCount(): int;

    public function getAverageAge(): ?float;

    public function getTotalRegistered(): int;

    public function getMaleRegistered(): int;

    public function getFemaleRegistered(): int;

    public function getMaleCount(): int;

    public function getFemaleCount(): int;

    public function getOtherGenderCount(): int;

    public function getTopCountiesByUsers(int $limit = 10): array;
}
