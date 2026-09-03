<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function exportQuery(array $filters = []): Builder;

    public function find(int $id): ?Candidate;

    public function findPotentialDuplicate(array $data): ?Candidate;

    public function create(array $data): Candidate;

    public function update(Candidate $candidate, array $data): bool;

    public function delete(Candidate $candidate): bool;

    public function allPositions(): Collection;

    public function allPoliticalParties(): Collection;

    public function allCounties(): Collection;

    public function allCountries(): Collection;

    public function allConstituencies(?string $county = null): Collection;

    public function allWards(?string $constituency = null): Collection;

    public function filterPublic(array $filters, int $perPage = 30): LengthAwarePaginator;

    public function publicPositionGroups(array $filters, int $perPage = 20): Collection;

    public function paginateApprovedForApi(array $filters, int $perPage = 12): LengthAwarePaginator;
    public function publicCountyGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection;

    public function publicAlternativeCountyGroups(array $filters, string $currentCounty): Collection;

    public function publicConstituencyGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection;

    public function publicWardGroups(array $filters, int $limit = 5, bool $includeEmpty = false, bool $withCandidates = true): Collection;

    public function loadPublicShow(Candidate $candidate): Candidate;
}

