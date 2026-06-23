<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\WardRepositoryInterface;
use App\Models\Ward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WardService
{
    public function __construct(
        private WardRepositoryInterface $wardRepository
    ) {}

    public function getPaginatedWards(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->wardRepository->paginate($perPage, $search);
    }

    public function getAllWards(): Collection
    {
        return $this->wardRepository->all();
    }

    public function createWard(array $data): Ward
    {
        return $this->wardRepository->create($data);
    }

    public function getOrderedConstituency():collection{
        return $this->wardRepository->getOrderedConstituency();
    }


    public function updateWard(Ward $ward, array $data): bool
    {
        return $this->wardRepository->update($ward, $data);
    }

    public function deleteWard(Ward $ward): bool
    {
        return $this->wardRepository->delete($ward);
    }

    public function importWards(array $wards): int
    {
        return $this->wardRepository->import($wards);
    }
}
