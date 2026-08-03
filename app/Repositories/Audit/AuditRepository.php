<?php

namespace App\Repositories\Audit;

use App\Contracts\Repositories\Audit\AuditRepositoryInterface;
use App\Models\Audit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditRepository implements AuditRepositoryInterface
{
    public function paginate(array $filters, ?int $candidateId = null): LengthAwarePaginator
    {
        return Audit::query()->with(['user', 'candidate'])
            ->when($candidateId !== null, fn ($q) => $q->where('candidate_id', $candidateId))
            ->when($filters['event'] ?? null, fn ($q, $v) => $q->where('event', $v))
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['actor_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['candidate_id'] ?? null, fn ($q, $v) => $q->where('candidate_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')->paginate(30)->withQueryString();
    }

    public function findForCandidate(int $id, ?int $candidateId = null): ?Audit
    {
        return Audit::query()->with(['user', 'candidate'])->when($candidateId !== null, fn ($q) => $q->where('candidate_id', $candidateId))->find($id);
    }

    public function create(array $attributes): Audit
    {
        return Audit::query()->create($attributes);
    }
}
