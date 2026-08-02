<?php

namespace App\Contracts\Repositories\Web;

use Illuminate\Support\Collection;

interface PublicPulseHomepageRepositoryInterface
{
    public function presidentialCandidates(): Collection;

    public function selections(): Collection;

    public function latestCompletedJobs(array $candidateIds): Collection;

    public function replaceSelections(array $rows): void;
}
