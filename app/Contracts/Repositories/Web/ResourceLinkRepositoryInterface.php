<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\County;
use App\Models\ResourceLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ResourceLinkRepositoryInterface
{
    public function paginateForCreator(int $userId, int $perPage = 12): LengthAwarePaginator;

    public function create(array $data): ResourceLink;

    public function findCandidateById(int $candidateId): ?Candidate;

    public function allCounties(): Collection;

    public function allConstituencies(?int $countyId = null): Collection;

    public function allWards(?int $constituencyId = null): Collection;

    public function allPoliticalParties(): Collection;

    public function approvedForCandidate(Candidate $candidate): Collection;

    public function approvedForCounty(County $county, int $limit = 50): Collection;
}
