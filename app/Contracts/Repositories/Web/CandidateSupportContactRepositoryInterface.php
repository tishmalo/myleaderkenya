<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CandidateSupportContact;
use Illuminate\Support\Collection;

interface CandidateSupportContactRepositoryInterface
{
    public function forCandidate(int $candidateId): Collection;

    public function create(array $data): CandidateSupportContact;

    public function update(CandidateSupportContact $contact, array $data): bool;

    public function delete(CandidateSupportContact $contact): bool;
}
