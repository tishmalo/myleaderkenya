<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenRateRepositoryInterface;
use App\Models\CandidateTokenRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CandidateTokenRateRepository implements CandidateTokenRateRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CandidateTokenRate::query()
            ->when(isset($filters['active']) && $filters['active'] !== '', fn ($query) => $query->where('is_active', (bool) $filters['active']))
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $query->where(function ($inner) use ($filters): void {
                    $inner->where('label', 'like', "%{$filters['search']}%")
                        ->orWhere('action_key', 'like', "%{$filters['search']}%");
                });
            })
            ->ordered()
            ->paginate($perPage);
    }

    public function active(): Collection
    {
        return CandidateTokenRate::active()->ordered()->get();
    }

    public function findActiveByActionKey(string $actionKey): ?CandidateTokenRate
    {
        return CandidateTokenRate::active()->where('action_key', $actionKey)->first();
    }

    public function create(array $data): CandidateTokenRate
    {
        return CandidateTokenRate::create($data);
    }

    public function update(CandidateTokenRate $rate, array $data): bool
    {
        return $rate->update($data);
    }

    public function delete(CandidateTokenRate $rate): bool
    {
        return $rate->delete();
    }
}
