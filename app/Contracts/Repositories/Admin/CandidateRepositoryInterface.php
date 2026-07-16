<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Candidate;

    public function update(Candidate $candidate, array $data): bool;

    public function delete(Candidate $candidate): bool;

    public function allPositions(): Collection;

    public function allPoliticalParties(): Collection;

    public function allCounties(): Collection;

    public function allCountries(): Collection;

    public function allConstituencies(?string $county = null): Collection;

    public function allWards(?string $constituency = null): Collection;

    public function filterPublic(array $filters, int $perPage = 16): LengthAwarePaginator;

    public function publicCountyGroups(array $filters, int $limit = 5, bool $includeEmpty = false): Collection;

    public function loadPublicShow(Candidate $candidate): Candidate;
}

