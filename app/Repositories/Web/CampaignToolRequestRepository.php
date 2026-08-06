<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignToolRequest;

class CampaignToolRequestRepository implements CampaignToolRequestRepositoryInterface
{
    public function create(array $data): CampaignToolRequest
    {
        return CampaignToolRequest::create($data);
    }

    public function syncSelectedTools(CampaignToolRequest $request, array $toolIds): void
    {
        $request->selectedTools()->sync($toolIds);
    }

    public function activeAdoptedToolIds(int $userId, int $candidateId, array $toolIds): array
    {
        return CampaignToolRequest::query()
            ->where('request_type', 'adoption')
            ->where('user_id', $userId)
            ->where('candidate_id', $candidateId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($toolIds): void {
                $query->whereIn('campaign_tool_id', $toolIds)
                    ->orWhereHas('selectedTools', fn ($selected) => $selected->whereIn('campaign_tools.id', $toolIds));
            })
            ->with('selectedTools:id')
            ->get()
            ->flatMap(fn (CampaignToolRequest $request) => collect([$request->campaign_tool_id])
                ->merge($request->selectedTools->pluck('id')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->intersect($toolIds)
            ->unique()
            ->values()
            ->all();
    }

    public function updateAdoptionStatus(int $userId, int $candidateId, string $status): void
    {
        CampaignToolRequest::query()
            ->where('request_type', 'adoption')
            ->where('user_id', $userId)
            ->where('candidate_id', $candidateId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => $status]);
    }
}