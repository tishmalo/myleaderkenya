<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateSupportContactRepositoryInterface;
use App\Models\CandidateSupportContact;
use Illuminate\Support\Collection;

class CandidateSupportContactRepository implements CandidateSupportContactRepositoryInterface
{
    public function forCandidate(int $candidateId): Collection
    {
        return CandidateSupportContact::with('groupType')
            ->where('candidate_id', $candidateId)
            ->latest()
            ->get();
    }

    public function create(array $data): CandidateSupportContact
    {
        return CandidateSupportContact::create($data);
    }

    public function update(CandidateSupportContact $contact, array $data): bool
    {
        return $contact->update($data);
    }

    public function delete(CandidateSupportContact $contact): bool
    {
        return $contact->delete();
    }
}
