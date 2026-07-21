<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateSmsBalanceRequestRepositoryInterface;
use App\Models\CandidateSmsBalanceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CandidateSmsBalanceRequestRepository implements CandidateSmsBalanceRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CandidateSmsBalanceRequest::query()
            ->with(['candidate.position', 'user'])
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $query->where(function ($inner) use ($filters): void {
                    $inner->where('message', 'like', "%{$filters['search']}%")
                        ->orWhereHas('candidate', fn ($candidate) => $candidate->where('name', 'like', "%{$filters['search']}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): CandidateSmsBalanceRequest
    {
        return CandidateSmsBalanceRequest::create($data);
    }

    public function update(CandidateSmsBalanceRequest $request, array $data): bool
    {
        return $request->update($data);
    }
}
