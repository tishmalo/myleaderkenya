<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Models\CampaignTool;
use App\Support\HomepageCache;
use Illuminate\Support\Facades\Cache;

class LandingService
{
    public function __construct(
        protected LandingRepositoryInterface $landingRepository,
        private PublicApprovalService $publicApprovalService
    ) {}

    /**
     * Get landing page data.
     *
     * @return array
     */
    public function getLandingData(): array
    {
        return Cache::remember(
            HomepageCache::key('landing-data-with-approval-tools-v2'),
            HomepageCache::ttl(),
            fn (): array => array_merge($this->landingRepository->getLandingStats(), [
                'publicApprovalCards' => $this->publicApprovalService->presidentialCards(),
                'landingCampaignTools' => CampaignTool::published()
                    ->ordered()
                    ->take(4)
                    ->get(),
            ])
        );
    }
}


