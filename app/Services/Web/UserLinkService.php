<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\ResourceLinkRepositoryInterface;
use App\Models\Candidate;
use App\Models\County;
use App\Models\ResourceLink;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserLinkService
{
    public function __construct(private ResourceLinkRepositoryInterface $links) {}

    public function listFor(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->links->paginateForCreator($user->getKey(), $perPage);
    }

    public function formData(array $selectedCandidateIds = []): array
    {
        $selected = null;

        if ($selectedCandidateIds !== []) {
            $candidate = $this->links->findCandidateById((int) $selectedCandidateIds[0]);

            $selected = $candidate ? $this->publicCandidateData($candidate) : null;
        }

        return [
            'counties' => $this->links->allCounties(),
            'constituencies' => $this->links->allConstituencies(),
            'wards' => $this->links->allWards(),
            'politicalParties' => $this->links->allPoliticalParties(),
            'platforms' => ResourceLink::PLATFORMS,
            'selectedCandidate' => $selected,
        ];
    }

    public function submit(User $user, array $data): ResourceLink
    {
        $candidateId = isset($data['candidate_id']) && $data['candidate_id'] !== ''
            ? (int) $data['candidate_id']
            : null;

        if ($candidateId !== null && ! $this->links->findCandidateById($candidateId)) {
            throw ValidationException::withMessages([
                'candidate_id' => 'Select an approved aspirant.',
            ]);
        }

        $data['candidate_id'] = $candidateId;
        $data['followers'] = isset($data['followers']) && $data['followers'] !== ''
            ? (int) $data['followers']
            : null;
        $data['user_id'] = $user->getKey();
        $data['approval_status'] = ResourceLink::STATUS_PENDING;

        return $this->links->create($data);
    }

    public function forCandidate(Candidate $candidate): Collection
    {
        return $this->links->approvedForCandidate($candidate);
    }

    public function forCounty(County $county): Collection
    {
        return $this->links->approvedForCounty($county);
    }

    /**
     * Mirrors the payload served by the public aspirant search so the same
     * search component can be reused on the submission form.
     */
    private function publicCandidateData(Candidate $candidate): array
    {
        return [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nickname' => $candidate->nick_name,
            'image_url' => $candidate->profile_picture ? Storage::url($candidate->profile_picture) : null,
            'position' => $candidate->position?->name,
            'party' => $candidate->politicalParty?->name,
            'jurisdiction' => collect([$candidate->ward, $candidate->constituency, $candidate->county, $candidate->country])
                ->filter()->unique()->implode(', '),
        ];
    }
}
