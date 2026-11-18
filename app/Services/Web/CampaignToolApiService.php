<?php

namespace App\Services\Web;

use App\Models\CampaignTool;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Services\Admin\CampaignToolService;
use App\Support\CampaignToolApiPresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CampaignToolApiService
{
    public function __construct(
        private CampaignToolService $tools,
        private CampaignToolFeatureRequestService $featureRequests,
        private AspirantWorkspaceService $workspace,
    ) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->tools->getPublishedTools($perPage)
            ->through(fn (CampaignTool $tool): array => CampaignToolApiPresenter::tool($tool, includeExcerpt: true));
    }

    /**
     * @return array<string, mixed>
     */
    public function show(CampaignTool $campaignTool): array
    {
        $this->ensurePublished($campaignTool);

        return CampaignToolApiPresenter::tool($campaignTool, includeExcerpt: true, includeContent: true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitFeatureRequest(CampaignTool $campaignTool, array $data, ?User $user): CampaignToolRequest
    {
        $this->ensurePublished($campaignTool);

        $candidate = $user ? $this->workspace->candidateForUser($user) : null;

        return $this->featureRequests->submit($campaignTool, $data, $user, $candidate);
    }

    private function ensurePublished(CampaignTool $campaignTool): void
    {
        abort_unless($campaignTool->status === 'published', 404);
    }
}
