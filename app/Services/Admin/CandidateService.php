<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CandidateService
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository
    ) {}

    // -------------------------------------------------------------------------
    // Admin CRUD
    // -------------------------------------------------------------------------

    public function getPaginatedCandidates(int $perPage = 15): LengthAwarePaginator
    {
        return $this->candidateRepository->paginate($perPage);
    }

    public function createCandidate(array $data, ?UploadedFile $picture = null): Candidate
    {
        if ($picture) {
            $data['profile_picture'] = $picture->store('candidates', 'public');
        }

        return $this->candidateRepository->create($data);
    }

    public function updateCandidate(Candidate $candidate, array $data, ?UploadedFile $picture = null): bool
    {
        if ($picture) {
            // Delete old picture before storing the new one
            if ($candidate->profile_picture) {
                Storage::disk('public')->delete($candidate->profile_picture);
            }
            $data['profile_picture'] = $picture->store('candidates', 'public');
        }

        return $this->candidateRepository->update($candidate, $data);
    }

    public function deleteCandidate(Candidate $candidate): bool
    {
        if ($candidate->profile_picture) {
            Storage::disk('public')->delete($candidate->profile_picture);
        }

        return $this->candidateRepository->delete($candidate);
    }

    // -------------------------------------------------------------------------
    // Form dropdowns
    // -------------------------------------------------------------------------

    public function getFormData(): array
    {
        return [
            'positions' => $this->candidateRepository->allPositions(),
            'politicalParties' => $this->candidateRepository->allPoliticalParties(),
        ];
    }

    // -------------------------------------------------------------------------
    // Public-facing pages
    // -------------------------------------------------------------------------

    public function getPublicIndex(array $filters, int $perPage = 16): array
    {
        return [
            'candidates' => $this->candidateRepository->filterPublic($filters, $perPage),
            'positions'  => $this->candidateRepository->allPositions(),
            'counties'   => $this->candidateRepository->allCounties(),
        ];
    }

    public function getPublicShow(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->loadPublicShow($candidate);
    }
}
