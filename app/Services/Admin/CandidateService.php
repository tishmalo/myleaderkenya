<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateSmsSettingRepositoryInterface;
use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CandidateService
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository,
        private CandidateSmsSettingRepositoryInterface $smsSettingRepository
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
        $smsSettings = $this->extractSmsSettings($data);
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            $data['profile_picture'] = $this->storeCandidatePicture($picture);
        }

        $candidate = $this->candidateRepository->create($data);
        $this->saveSmsSettings($candidate, $smsSettings);

        return $candidate;
    }

    public function updateCandidate(Candidate $candidate, array $data, ?UploadedFile $picture = null): bool
    {
        $smsSettings = $this->extractSmsSettings($data);
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            if ($candidate->profile_picture) {
                $this->deleteCandidatePicture($candidate->profile_picture);
            }
            $data['profile_picture'] = $this->storeCandidatePicture($picture);
        }

        $updated = $this->candidateRepository->update($candidate, $data);
        $this->saveSmsSettings($candidate, $smsSettings);

        return $updated;
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
        unset($data['sms_enabled'], $data['sms_provider'], $data['sms_base_url'], $data['sms_sender_name'], $data['sms_username'], $data['sms_password']);

        if (! Schema::hasColumn('candidates', 'political_party_id')) {
            unset($data['political_party_id']);
        }

        if (! Schema::hasColumn('candidates', 'user_id')) {
            unset($data['user_id']);
        }

        if (! Schema::hasColumn('candidates', 'approval_status')) {
            unset($data['approval_status']);
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

    private function extractSmsSettings(array $data): array
    {
        return [
            'enabled' => (bool) ($data['sms_enabled'] ?? false),
            'provider' => $data['sms_provider'] ?? 'infobip',
            'base_url' => $data['sms_base_url'] ?? null,
            'sender_name' => $data['sms_sender_name'] ?? null,
            'username' => $data['sms_username'] ?? null,
            'password' => $data['sms_password'] ?? null,
        ];
    }

    private function saveSmsSettings(Candidate $candidate, array $settings): void
    {
        if (! Schema::hasTable('candidate_sms_settings')) {
            return;
        }

        $existing = $this->smsSettingRepository->findForCandidate($candidate);

        if (blank($settings['password'] ?? null) && $existing) {
            unset($settings['password']);
        }

        if (
            ! $settings['enabled']
            && blank($settings['base_url'] ?? null)
            && blank($settings['sender_name'] ?? null)
            && blank($settings['username'] ?? null)
            && blank($settings['password'] ?? null)
            && ! $existing
        ) {
            return;
        }

        $this->smsSettingRepository->upsertForCandidate($candidate, $settings);
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
        $showCountyGroups = empty($filters['county'])
            && ! empty($filters['bloc'])
            && $this->usesCountyLanding($filters['position'] ?? null);

        $showCountyAspirantGroups = empty($filters['county'])
            && ! $showCountyGroups
            && $this->usesCountyAspirantGroups($filters['position'] ?? null);

        return [
            'candidates' => $this->candidateRepository->filterPublic($filters, $perPage),
            'countyGroups' => ($showCountyGroups || $showCountyAspirantGroups)
                ? $this->candidateRepository->publicCountyGroups($filters, 5, $showCountyGroups)
                : collect(),
            'showCountyGroups' => $showCountyGroups,
            'showCountyAspirantGroups' => $showCountyAspirantGroups,
            'positions'  => $this->candidateRepository->allPositions(),
            'politicalParties' => $this->candidateRepository->allPoliticalParties(),
            'countries' => $this->candidateRepository->allCountries(),
            'counties'   => $this->candidateRepository->allCounties(),
            'constituencies' => $this->candidateRepository->allConstituencies($filters['county'] ?? null),
            'wards' => $this->candidateRepository->allWards($filters['constituency'] ?? null),
        ];
    }

    private function usesCountyLanding($position): bool
    {
        if (blank($position)) {
            return false;
        }

        $position = trim((string) $position);

        if (! is_numeric($position)) {
            $positionKey = strtolower(str_replace(['_', ' '], '-', $position));

            return in_array($positionKey, ['mp', 'member-of-parliament', 'mca', 'member-of-county-assembly'], true);
        }

        $matchedPosition = $this->candidateRepository->allPositions()
            ->firstWhere('id', (int) $position);

        if (! $matchedPosition) {
            return false;
        }

        $name = strtolower(trim($matchedPosition->name));

        return $name === 'mp'
            || $name === 'mca'
            || str_contains($name, 'member of parliament')
            || str_contains($name, 'member of county assembly');
    }

    private function usesCountyAspirantGroups($position): bool
    {
        if (blank($position)) {
            return false;
        }

        $position = trim((string) $position);

        if (! is_numeric($position)) {
            $positionKey = strtolower(str_replace(['_', ' '], '-', $position));

            return in_array($positionKey, [
                'governor',
                'senator',
                'women-rep',
                'woman-rep',
                'women-representative',
                'woman-representative',
            ], true);
        }

        $matchedPosition = $this->candidateRepository->allPositions()
            ->firstWhere('id', (int) $position);

        if (! $matchedPosition) {
            return false;
        }

        $name = strtolower(trim($matchedPosition->name));

        return $name === 'governor'
            || $name === 'senator'
            || str_contains($name, 'women rep')
            || str_contains($name, 'woman rep')
            || str_contains($name, 'women representative')
            || str_contains($name, 'woman representative');
    }

    public function getPublicShow(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->loadPublicShow($candidate);
    }
}




