<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CampaignToolFeatureRequestService
{
    public function __construct(
        private CampaignToolRequestRepositoryInterface $toolRequests
    ) {}

    public function submit(CampaignTool $campaignTool, array $data, ?User $user, ?Candidate $candidate): CampaignToolRequest
    {
        $selectedToolIds = collect($data['other_campaign_tool_ids'] ?? [])
            ->map(fn ($toolId) => (int) $toolId)
            ->reject(fn (int $toolId) => $toolId === $campaignTool->id)
            ->unique()
            ->values()
            ->all();

        unset($data['feature_request_tool_id'], $data['other_campaign_tool_ids']);

        $spamReason = null;

        if ($this->toolRequests->hasPendingFeatureRequest($campaignTool, $data)) {
            $spamReason = 'duplicate_pending_request';
        }

        return DB::transaction(function () use ($data, $campaignTool, $user, $candidate, $selectedToolIds, $spamReason): CampaignToolRequest {
            $featureRequest = $this->toolRequests->create($data + [
                'campaign_tool_id' => $campaignTool->id,
                'user_id' => $user?->id,
                'candidate_id' => $candidate?->id,
                'request_type' => 'feature',
                'tool_title' => $campaignTool->title,
                'status' => 'new',
                'is_spam' => $spamReason !== null,
                'spam_reason' => $spamReason,
            ]);

            $this->toolRequests->syncSelectedTools($featureRequest, $selectedToolIds);

            return $featureRequest;
        });
    }
}
