<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CandidateService
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository
    ) {}

    // -------------------------------------------------------------------------
    // Admin CRUD
    // -------------------------------------------------------------------------

    public function getPaginatedCandidates(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->candidateRepository->paginate($perPage, $filters);
    }

    public function createCandidate(array $data, ?UploadedFile $picture = null): Candidate
    {
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            $data['profile_picture'] = $this->storeCandidatePicture($picture);
        }

        return $this->candidateRepository->create($data);
    }

    public function updateCandidate(Candidate $candidate, array $data, ?UploadedFile $picture = null): bool
    {
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            // Delete old picture before storing the new one
            if ($candidate->profile_picture) {
                $this->deleteCandidatePicture($candidate->profile_picture);
            }
            $data['profile_picture'] = $this->storeCandidatePicture($picture);
        }

        return $this->candidateRepository->update($candidate, $data);
    }

    public function deleteCandidate(Candidate $candidate): bool
    {
        if ($candidate->profile_picture) {
            $this->deleteCandidatePicture($candidate->profile_picture);
        }

        return $this->candidateRepository->delete($candidate);
    }

    private function storeCandidatePicture(UploadedFile $picture): string
    {
        $directory = storage_path('app/public/candidates');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = $picture->getClientOriginalExtension() ?: $picture->extension() ?: 'jpg';
        $filename = Str::uuid()->toString() . '.' . strtolower($extension);

        $picture->move($directory, $filename);

        return 'candidates/' . $filename;
    }

    private function deleteCandidatePicture(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = storage_path('app/public/' . ltrim($path, '/'));

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function normalizeCandidateData(array $data): array
    {
        if (! Schema::hasColumn('candidates', 'political_party_id')) {
            unset($data['political_party_id']);
        }

        foreach (['country', 'county', 'constituency', 'ward'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeLocationValue($data[$field]);
            }
        }

        return $data;
    }

    private function normalizeLocationValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value['name'] ?? $value['label'] ?? null;
        }

        if (is_object($value)) {
            return $value->name ?? $value->label ?? null;
        }

        $value = trim((string) $value);
        if ($value === '[object Object]') {
            return null;
        }

        if (str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded['name'] ?? $decoded['label'] ?? null;
            }
        }

        return $value;
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
            'politicalParties' => $this->candidateRepository->allPoliticalParties(),
            'counties'   => $this->candidateRepository->allCounties(),
        ];
    }

    public function getPublicShow(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->loadPublicShow($candidate);
    }
}
