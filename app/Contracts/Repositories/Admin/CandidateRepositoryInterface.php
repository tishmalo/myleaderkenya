<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CandidateRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Candidate;

    public function update(Candidate $candidate, array $data): bool;

    public function delete(Candidate $candidate): bool;

    public function allPositions(): Collection;

    public function allBlocs(): Collection;

    public function allCounties(): Collection;

    public function filterPublic(array $filters, int $perPage = 16): LengthAwarePaginator;

    public function loadPublicShow(Candidate $candidate): Candidate;
}
