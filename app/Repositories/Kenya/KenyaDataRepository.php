<?php

namespace App\Repositories\Kenya;

use App\Contracts\Repositories\Kenya\CountyRepositoryInterface;

class KenyaDataRepository implements CountyRepositoryInterface
{
    protected array $counties;
    protected array $constituencies;

    public function __construct()
    {
        $this->counties = config('kenya.counties');
        $this->constituencies = config('kenya.constituencies');
    }

    public function getAllCounties(): array
    {
        return $this->counties;
    }

    public function getConstituenciesByCounty(string $county): array
    {
        return $this->constituencies[$county] ?? [];
    }
}