<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\LandingRepositoryInterface;

class LandingService
{
    public function __construct(
        protected LandingRepositoryInterface $landingRepository
    ) {}

    /**
     * Get landing page data.
     *
     * @return array
     */
    public function getLandingData(): array
    {
        return $this->landingRepository->getLandingStats();
    }
}
