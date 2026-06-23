<?php

namespace App\Contracts\Repositories\Kenya;



interface CountyRepositoryInterface
{
    public function getAllCounties(): array;
    public function getConstituenciesByCounty(string $county): array;
}