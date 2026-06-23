<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CountyRepositoryInterface;
use App\Models\County;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CountyService
{
    public function __construct(
        private CountyRepositoryInterface $countyRepository
    ) {}

    public function getPaginatedCounties(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->countyRepository->paginate($perPage, $search);
    }

    public function getOrderedBlocs(){

    
        return $this->countyRepository->getOrderedBlocs();
    }


    public function getAllCounties(): Collection
    {
        return $this->countyRepository->all();
    }

    public function createCounty(array $data): County
    {
        return $this->countyRepository->create($data);
    }

    public function updateCounty(County $county, array $data): County
    {
        return $this->countyRepository->update($county, $data);
    }

    public function deleteCounty(County $county): bool
    {
        return $this->countyRepository->delete($county);
    }

    public function importCounties(array $counties): int
    {
        return $this->countyRepository->import($counties);
    }
}
