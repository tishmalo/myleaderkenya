<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Models\CandidateTokenPackage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CandidateTokenPackageRepository implements CandidateTokenPackageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CandidateTokenPackage::query()
            ->when(isset($filters['active']) && $filters['active'] !== '', fn ($query) => $query->where('is_active', (bool) $filters['active']))
            ->when(! empty($filters['search']), fn ($query) => $query->where('name', 'like', "%{$filters['search']}%"))
            ->ordered()
            ->paginate($perPage);
    }

    public function active(): Collection
    {
        return CandidateTokenPackage::active()->ordered()->get();
    }

    public function findActive(int $id): CandidateTokenPackage
    {
        return CandidateTokenPackage::active()->findOrFail($id);
    }

    public function create(array $data): CandidateTokenPackage
    {
        return CandidateTokenPackage::create($data);
    }

    public function update(CandidateTokenPackage $package, array $data): bool
    {
        return $package->update($data);
    }

    public function delete(CandidateTokenPackage $package): bool
    {
        return $package->delete();
    }
}
