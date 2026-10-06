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

    /**
     * Updates a link's content and targeting. The approval status is
     * deliberately left alone - an admin fixing a typo should not have to
     * re-approve the link.
     */
    public function update(ResourceLink $resourceLink, array $data): bool
    {
        $candidateId = isset($data['candidate_id']) && $data['candidate_id'] !== ''
            ? (int) $data['candidate_id']
            : null;

        return $resourceLink->update([
            'platform' => $data['platform'],
            'title' => $data['title'],
            'url' => $data['url'],
            'county_id' => $data['county_id'],
            'constituency_id' => $data['constituency_id'] ?? null,
            'ward_id' => $data['ward_id'] ?? null,
            'political_party_id' => $data['political_party_id'] ?? null,
            'candidate_id' => $candidateId,
            'followers' => isset($data['followers']) && $data['followers'] !== ''
                ? (int) $data['followers']
                : null,
            'comment' => $data['comment'] ?? null,
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
