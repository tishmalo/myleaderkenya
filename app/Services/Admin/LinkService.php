<?php

namespace App\Services\Admin;

use App\Models\ResourceLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LinkService
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ResourceLink::query()
            ->with(['county:id,name', 'constituency:id,name', 'ward:id,name', 'politicalParty:id,name', 'candidate:id,name', 'creator:id,name'])
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('approval_status', $status))
            ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function review(ResourceLink $resourceLink, string $status, int $reviewedBy): bool
    {
        return $resourceLink->update([
            'approval_status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);
    }

    public function delete(ResourceLink $resourceLink): bool
    {
        return $resourceLink->delete();
    }

    public function countPending(): int
    {
        return ResourceLink::query()->where('approval_status', ResourceLink::STATUS_PENDING)->count();
    }

    public function statuses(): Collection
    {
        return collect([
            ResourceLink::STATUS_PENDING => 'Pending review',
            ResourceLink::STATUS_APPROVED => 'Approved',
            ResourceLink::STATUS_REJECTED => 'Rejected',
        ]);
    }
}
