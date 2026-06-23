<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\ConstituencyRepositoryInterface;
use App\Models\Constituency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ConstituencyService
{
    public function __construct(
        private ConstituencyRepositoryInterface $constituencyRepository
    ) {}

    public function getPaginatedConstituencies(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->constituencyRepository->paginate($perPage, $search);
    }

    public function getAllConstituencies(): Collection
    {
        return $this->constituencyRepository->all();
    }

    public function createConstituency(array $data): Constituency
    {
        return $this->constituencyRepository->create($data);
    }

    public function updateConstituency(Constituency $constituency, array $data): bool
    {
        return $this->constituencyRepository->update($constituency, $data);
    }

    public function getOrderedCounties()
    {
        return $this->constituencyRepository->getOrderedCounties();
    }

    public function deleteConstituency(Constituency $constituency): bool
    {
        return $this->constituencyRepository->delete($constituency);
    }

    public function importConstituencies(array $constituencies): int
    {
        return $this->constituencyRepository->import($constituencies);
    }
}
