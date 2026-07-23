<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;
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
            HomepageCache::key('landing-data-with-approval-v8'),
            HomepageCache::ttl(),
            fn (): array => array_merge($this->landingRepository->getLandingStats(), [
                'publicApprovalCards' => $this->publicApprovalService->presidentialCards(),
            ])
        );
    }
}
