<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CampaignToolRequestRepositoryInterface;
use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignToolFeatureRequestService
{
    public function __construct(
        private CampaignToolRequestRepositoryInterface $toolRequests,
        private SpamFilterService $spamFilter
    ) {}

    public function submit(CampaignTool $campaignTool, array $data, ?User $user, ?Candidate $candidate): CampaignToolRequest
    {
        $ip = request()->ip();

        $contentReason = $this->spamFilter->inspect($data);

        if ($contentReason !== null && config('spam_filter.content_action', 'reject') === 'reject') {
            $this->spamFilter->recordSample($data, $contentReason, $ip, 'request');

            throw ValidationException::withMessages([
                'requested_feature' => 'Your submission was flagged as spam and was not accepted.',
            ]);
        }

        $ipPolicy = $this->spamFilter->ipPolicy($ip);

        if ($ipPolicy === 'reject') {
            $this->spamFilter->recordSample($data, 'non_kenyan_ip', $ip, 'request');

            throw ValidationException::withMessages([
                'requested_feature' => 'Your submission was flagged as spam and was not accepted.',
            ]);
        }

        $selectedToolIds = collect($data['other_campaign_tool_ids'] ?? [])
            ->map(fn ($toolId) => (int) $toolId)
            ->reject(fn (int $toolId) => $toolId === $campaignTool->id)
            ->unique()
            ->values()
            ->all();

        unset($data['feature_request_tool_id'], $data['other_campaign_tool_ids']);

        $spamReason = $contentReason
            ?? ($ipPolicy === 'spam' ? 'non_kenyan_ip' : null);

        if ($spamReason === null && $this->toolRequests->hasPendingFeatureRequest($campaignTool, $data)) {
            $spamReason = 'duplicate_pending_request';
        }

        return DB::transaction(function () use ($data, $campaignTool, $user, $candidate, $selectedToolIds, $spamReason, $ip): CampaignToolRequest {
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

            if ($spamReason !== null) {
                $this->spamFilter->recordSample($data, $spamReason, $ip, 'request', $featureRequest->id);
            }

            $this->toolRequests->syncSelectedTools($featureRequest, $selectedToolIds);

            return $featureRequest;
        });
    }
}