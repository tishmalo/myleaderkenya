<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenTransactionRepositoryInterface;
use App\Models\CandidateTokenTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CandidateTokenTransactionRepository implements CandidateTokenTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return CandidateTokenTransaction::query()
            ->with(['candidate.position', 'user'])
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $query->where(function ($inner) use ($filters): void {
                    $inner->where('action_label', 'like', "%{$filters['search']}%")
                        ->orWhere('action_key', 'like', "%{$filters['search']}%")
                        ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', "%{$filters['search']}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): CandidateTokenTransaction
    {
        return CandidateTokenTransaction::create($data);
    }

    public function update(CandidateTokenTransaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }
}
