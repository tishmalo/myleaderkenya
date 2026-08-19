<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CampaignToolRequestRepository implements CampaignToolRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return CampaignToolRequest::with(['campaignTool', 'package', 'payment', 'selectedTools:id,title', 'candidate.position', 'user'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['request_type'] ?? null, fn ($query, $type) => $query->where('request_type', $type))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['campaign_tool_id'] ?? null, function ($query, $toolId): void {
                $query->where(function ($toolQuery) use ($toolId): void {
                    $toolQuery->where('campaign_tool_id', $toolId)
                        ->orWhereHas('selectedTools', fn ($selected) => $selected->where('campaign_tools.id', $toolId));
                });
            })
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('requester_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('requested_feature', 'like', "%{$search}%")
                        ->orWhere('tool_key', 'like', "%{$search}%")
                        ->orWhere('tool_title', 'like', "%{$search}%")
                        ->orWhere('disabled_reason', 'like', "%{$search}%")
                        ->orWhere('use_case', 'like', "%{$search}%")
                        ->orWhereHas('selectedTools', fn ($toolQuery) => $toolQuery->where('title', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function toolOptions(): Collection
    {
        return CampaignTool::ordered()->get(['id', 'title']);
    }

    public function update(CampaignToolRequest $request, array $data): bool
    {
        return $request->update($data);
    }

    public function delete(CampaignToolRequest $request): bool
    {
        return $request->delete();
    }
}
