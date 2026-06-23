<?php

namespace App\Contracts\Repositories\Web;

interface LandingRepositoryInterface
{
    /**
     * Get statistics for the landing page.
     *
     * @return array
     */
    public function getLandingStats(): array;
}
