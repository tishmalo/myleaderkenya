<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateSmsSettingRepositoryInterface;
use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\SupportGroupType;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Ward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CandidateService
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository,
        private CandidateSmsSettingRepositoryInterface $smsSettingRepository,
        private SettingService $settingService
    ) {}

    // -------------------------------------------------------------------------
    // Admin CRUD
    // -------------------------------------------------------------------------

    public function getPaginatedCandidates(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->candidateRepository->paginate($perPage, $filters);
    }

    public function createCandidate(array $data, ?UploadedFile $picture = null, ?UploadedFile $coverPhoto = null, ?UploadedFile $campaignPoster = null, ?UploadedFile $campaignVideo = null, ?UploadedFile $campaignSkizaAudio = null): Candidate
    {
        $smsSettings = $this->extractSmsSettings($data);
        $supportContacts = $this->extractSupportContacts($data);
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            $data['profile_picture'] = $this->storeCandidateImage($picture, 'candidates');
        }

        if ($coverPhoto) {
            $data['cover_photo'] = $this->storeCandidateImage($coverPhoto, 'candidates/covers');
        }

        if ($campaignPoster) {
            $data['campaign_poster'] = $this->storeCandidateImage($campaignPoster, 'candidates/posters');
        }

        if ($campaignVideo) {
            $data['campaign_video'] = $this->storeCandidateImage($campaignVideo, 'candidates/videos');
        }

        if ($campaignSkizaAudio) {
            $data['campaign_skiza_audio'] = $this->storeCandidateImage($campaignSkizaAudio, 'candidates/audio');
        }

        $candidate = $this->candidateRepository->create($data);
        $this->saveSmsSettings($candidate, $smsSettings);
        $this->syncSupportContacts($candidate, $supportContacts);

        return $candidate;
    }

    public function updateCandidate(Candidate $candidate, array $data, ?UploadedFile $picture = null, ?UploadedFile $coverPhoto = null, ?UploadedFile $campaignPoster = null, ?UploadedFile $campaignVideo = null, ?UploadedFile $campaignSkizaAudio = null): bool
    {
        $smsSettings = $this->extractSmsSettings($data);
        $supportContacts = $this->extractSupportContacts($data);
        $data = $this->normalizeCandidateData($data);

        if ($picture) {
            if ($candidate->profile_picture) {
                $this->deleteCandidatePicture($candidate->profile_picture);
            }
            $data['profile_picture'] = $this->storeCandidateImage($picture, 'candidates');
        }

        if ($coverPhoto) {
            if ($candidate->cover_photo) {
                $this->deleteCandidatePicture($candidate->cover_photo);
            }
            $data['cover_photo'] = $this->storeCandidateImage($coverPhoto, 'candidates/covers');
        }

        if ($campaignPoster) {
            if ($candidate->campaign_poster) {
                $this->deleteCandidatePicture($candidate->campaign_poster);
            }
            $data['campaign_poster'] = $this->storeCandidateImage($campaignPoster, 'candidates/posters');
        }

        if ($campaignVideo) {
            if ($candidate->campaign_video) {
                $this->deleteCandidatePicture($candidate->campaign_video);
            }
            $data['campaign_video'] = $this->storeCandidateImage($campaignVideo, 'candidates/videos');
        }

        if ($campaignSkizaAudio) {
            if ($candidate->campaign_skiza_audio) {
                $this->deleteCandidatePicture($candidate->campaign_skiza_audio);
            }
            $data['campaign_skiza_audio'] = $this->storeCandidateImage($campaignSkizaAudio, 'candidates/audio');
        }

        $updated = $this->candidateRepository->update($candidate, $data);
        $this->saveSmsSettings($candidate, $smsSettings);
        $this->syncSupportContacts($candidate, $supportContacts);

        return $updated;
    }

    public function deleteCandidate(Candidate $candidate): bool
    {
        if ($candidate->profile_picture) {
            $this->deleteCandidatePicture($candidate->profile_picture);
        }

        if ($candidate->cover_photo) {
            $this->deleteCandidatePicture($candidate->cover_photo);
        }

        if ($candidate->campaign_poster) {
            $this->deleteCandidatePicture($candidate->campaign_poster);
        }

        if ($candidate->campaign_video) {
            $this->deleteCandidatePicture($candidate->campaign_video);
        }

        if ($candidate->campaign_skiza_audio) {
            $this->deleteCandidatePicture($candidate->campaign_skiza_audio);
        }

        return $this->candidateRepository->delete($candidate);
    }
    private function storeCandidateImage(UploadedFile $picture, string $directoryName): string
    {
        $directory = storage_path('app/public/' . trim($directoryName, '/'));

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = $picture->getClientOriginalExtension() ?: $picture->extension() ?: 'jpg';
        $filename = Str::uuid()->toString() . '.' . strtolower($extension);

        $picture->move($directory, $filename);

        return trim($directoryName, '/') . '/' . $filename;
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
        unset($data['support_contacts'], $data['sms_enabled'], $data['sms_provider'], $data['sms_base_url'], $data['sms_sender_name'], $data['sms_username'], $data['sms_password'], $data['profile_picture'], $data['cover_photo'], $data['campaign_poster'], $data['campaign_video'], $data['campaign_skiza_audio']);

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

    private function extractSupportContacts(array $data): ?array
    {
        if (! array_key_exists('support_contacts', $data)) {
            return null;
        }

        return collect((array) $data['support_contacts'])
            ->filter(function (array $contact): bool {
                return filled($contact['support_group_type_id'] ?? null)
                    || filled($contact['name'] ?? null)
                    || filled($contact['email'] ?? null)
                    || filled($contact['phone'] ?? null);
            })
            ->values()
            ->all();
    }

    private function syncSupportContacts(Candidate $candidate, ?array $contacts): void
    {
        if ($contacts === null) {
            return;
        }

        $keptIds = [];

        foreach ($contacts as $contact) {
            $payload = [
                'support_group_type_id' => (int) $contact['support_group_type_id'],
                'name' => trim((string) $contact['name']),
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'] ?? null,
            ];

            $contactId = (int) ($contact['id'] ?? 0);
            $model = $contactId > 0
                ? $candidate->supportContacts()->whereKey($contactId)->first()
                : null;

            if ($model) {
                $model->fill($payload)->save();
            } else {
                $model = $candidate->supportContacts()->create($payload);
            }

            $keptIds[] = $model->id;
        }

        $candidate->supportContacts()
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
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
            'supportGroupTypes' => SupportGroupType::active()->ordered()->get(),
        ];
    }

    // -------------------------------------------------------------------------
    // Public-facing pages
    // -------------------------------------------------------------------------

    public function getPublicIndex(array $filters, int $perPage = 16): array
    {
        $showCountyGroups = empty($filters['county'])
            && $this->usesCountyLanding($filters['position'] ?? null);

        $showConstituencyGroups = ! empty($filters['county'])
            && empty($filters['constituency'])
            && ($this->isMpPosition($filters['position'] ?? null) || $this->isMcaPosition($filters['position'] ?? null));

        $showWardGroups = ! empty($filters['constituency'])
            && empty($filters['ward'])
            && $this->isMcaPosition($filters['position'] ?? null);

        $showLocationGroups = $showCountyGroups || $showConstituencyGroups || $showWardGroups;

        $locationGroups = collect();
        $locationGroupLabel = 'counties';
        if ($showCountyGroups) {
            $locationGroups = $this->candidateRepository->publicCountyGroups($filters, 5, true, false);
            $locationGroupLabel = 'counties';
        } elseif ($showConstituencyGroups) {
            $locationGroups = $this->candidateRepository->publicConstituencyGroups($filters, 5, true, false);
            $locationGroupLabel = 'constituencies';
        } elseif ($showWardGroups) {
            $locationGroups = $this->candidateRepository->publicWardGroups($filters, 5, true, false);
            $locationGroupLabel = 'wards';
        }

        $showCountyAspirantGroups = empty($filters['county'])
            && ! $showCountyGroups
            && $this->usesCountyAspirantGroups($filters['position'] ?? null);

        $showConstituencyAspirantGroups = false;

        $showWardAspirantGroups = false;

        $showAspirantGroups = $showCountyAspirantGroups
            || $showConstituencyAspirantGroups
            || $showWardAspirantGroups;

        $aspirantGroups = collect();
        if ($showCountyAspirantGroups) {
            $aspirantGroups = $this->candidateRepository->publicCountyGroups($filters, 5);
        } elseif ($showConstituencyAspirantGroups) {
            $aspirantGroups = $this->candidateRepository->publicConstituencyGroups($filters, 5);
        } elseif ($showWardAspirantGroups) {
            $aspirantGroups = $this->candidateRepository->publicWardGroups($filters, 5);
        }

        return [
            'candidates' => $showLocationGroups || $showAspirantGroups
                ? new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, $perPage)
                : $this->candidateRepository->filterPublic($filters, $perPage),
            'countyGroups' => $showCountyGroups ? $locationGroups : collect(),
            'locationGroups' => $locationGroups,
            'locationGroupLabel' => $locationGroupLabel,
            'showLocationGroups' => $showLocationGroups,
            'aspirantGroups' => $aspirantGroups,
            'showCountyGroups' => $showCountyGroups,
            'showConstituencyGroups' => $showConstituencyGroups,
            'showWardGroups' => $showWardGroups,
            'showCountyAspirantGroups' => $showCountyAspirantGroups,
            'showConstituencyAspirantGroups' => $showConstituencyAspirantGroups,
            'showWardAspirantGroups' => $showWardAspirantGroups,
            'showAspirantGroups' => $showAspirantGroups,
            'positions'  => $this->candidateRepository->allPositions(),
            'politicalParties' => $this->candidateRepository->allPoliticalParties(),
            'supportGroupTypes' => SupportGroupType::active()->ordered()->get(),
            'countries' => $this->candidateRepository->allCountries(),
            'counties'   => $this->candidateRepository->allCounties(),
            'constituencies' => $this->candidateRepository->allConstituencies($filters['county'] ?? null),
            'wards' => $this->candidateRepository->allWards($filters['constituency'] ?? null),
            'aspirantSeo' => $this->aspirantSeo($filters),
        ];
    }

    private function usesCountyLanding($position): bool
    {
        return filled($position) && ! $this->isPresidentialPosition($position);
    }

    private function isPresidentialPosition($position): bool
    {
        if (blank($position)) {
            return false;
        }

        $position = trim((string) $position);

        if (! is_numeric($position)) {
            $positionKey = strtolower(str_replace(['_', ' '], '-', $position));

            return in_array($positionKey, ['president', 'presidential'], true);
        }

        $matchedPosition = $this->candidateRepository->allPositions()
            ->firstWhere('id', (int) $position);

        if (! $matchedPosition) {
            return false;
        }

        $name = strtolower(trim($matchedPosition->name));

        return $name === 'president' || str_contains($name, 'president');
    }

    private function isMpPosition($position): bool
    {
        return $this->positionMatches($position, ['mp', 'member-of-parliament'], ['mp', 'member of parliament']);
    }

    private function isMcaPosition($position): bool
    {
        return $this->positionMatches($position, ['mca', 'member-of-county-assembly'], ['mca', 'member of county assembly']);
    }

    private function positionMatches($position, array $keys, array $names): bool
    {
        if (blank($position)) {
            return false;
        }

        $position = trim((string) $position);

        if (! is_numeric($position)) {
            $positionKey = strtolower(str_replace(['_', ' '], '-', $position));

            return in_array($positionKey, $keys, true);
        }

        $matchedPosition = $this->candidateRepository->allPositions()
            ->firstWhere('id', (int) $position);

        if (! $matchedPosition) {
            return false;
        }

        $name = strtolower(trim($matchedPosition->name));

        foreach ($names as $needle) {
            if ($name === $needle || str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
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

    private function aspirantSeo(array $filters): array
    {
        $page = $this->settingService->getFrontendPage('aspirants');
        $content = $page['content'];
        $tokens = $this->aspirantSeoTokens($filters);

        $heading = $this->replaceSeoTokens($content['hero_title'] ?? '', $tokens);
        $description = $this->replaceSeoTokens($content['excerpt'] ?? '', $tokens);
        $metaTitle = $this->replaceSeoTokens($content['meta_title'] ?? '', $tokens);
        $metaDescription = $this->replaceSeoTokens($content['meta_description'] ?? '', $tokens);

        return [
            'heading' => $heading ?: $tokens['region'] . ' ' . $tokens['position'] . ' Aspirants',
            'description' => $description ?: 'Meet the candidates and aspirants seeking to represent ' . $tokens['region'] . '.',
            'meta_title' => $metaTitle ?: $tokens['region'] . ' ' . $tokens['position'] . ' Candidates and Aspirants ' . $tokens['year'] . ' Kenya Elections',
            'meta_description' => $metaDescription ?: 'Find ' . $tokens['region'] . ' ' . $tokens['position'] . ' candidates and aspirants for the ' . $tokens['year'] . ' Kenya elections.',
        ];
    }

    private function aspirantSeoTokens(array $filters): array
    {
        $region = $this->seoRegionLabel($filters);
        $position = $this->seoPositionLabel($filters['position'] ?? null);

        return [
            'region' => $region,
            'area' => $region,
            'position' => $position,
            'year' => '2027',
        ];
    }

    private function replaceSeoTokens(string $template, array $tokens): string
    {
        if ($template === '') {
            return '';
        }

        return trim(strtr($template, [
            '{region}' => $tokens['region'],
            '{area}' => $tokens['area'],
            '{position}' => $tokens['position'],
            '{year}' => $tokens['year'],
        ]));
    }

    private function seoRegionLabel(array $filters): string
    {
        if (! empty($filters['ward'])) {
            return $this->modelName(Ward::class, $filters['ward']) ?: trim((string) $filters['ward']);
        }

        if (! empty($filters['constituency'])) {
            $name = $this->modelName(Constituency::class, $filters['constituency']) ?: trim((string) $filters['constituency']);
            return Str::contains(Str::lower($name), 'constituency') ? $name : $name . ' Constituency';
        }

        if (! empty($filters['county'])) {
            $name = $this->modelName(County::class, $filters['county']) ?: trim((string) $filters['county']);
            return Str::contains(Str::lower($name), 'county') ? $name : $name . ' County';
        }

        if (! empty($filters['bloc'])) {
            return $this->modelName(Bloc::class, $filters['bloc']) ?: 'Selected Region';
        }

        if (! empty($filters['country'])) {
            return trim((string) $filters['country']);
        }

        return 'Kenya';
    }

    private function modelName(string $modelClass, $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $query = $modelClass::query();

        if (is_numeric($value)) {
            return $query->whereKey((int) $value)->value('name');
        }

        return $query->where('name', $value)->value('name');
    }

    private function seoPositionLabel($position): string
    {
        if (blank($position)) {
            return 'Presidential';
        }

        $positionName = trim((string) $position);
        if (is_numeric($position)) {
            $matchedPosition = $this->candidateRepository->allPositions()
                ->firstWhere('id', (int) $position);
            $positionName = trim((string) ($matchedPosition->name ?? $positionName));
        }

        $key = strtolower(str_replace(['_', ' '], '-', $positionName));

        return match (true) {
            in_array($key, ['president', 'presidential'], true) || str_contains($key, 'president') => 'Presidential',
            str_contains($key, 'governor') => 'Gubernatorial',
            str_contains($key, 'senator') => 'Senatorial',
            str_contains($key, 'women-rep'), str_contains($key, 'woman-rep'), str_contains($key, 'representative') => 'Women Representative',
            $key === 'mp' || str_contains($key, 'member-of-parliament') => 'Parliamentary',
            $key === 'mca' || str_contains($key, 'county-assembly') => 'MCA',
            default => Str::headline(str_replace('-', ' ', $key)),
        };
    }
    public function getPublicShow(Candidate $candidate): Candidate
    {
        return $this->candidateRepository->loadPublicShow($candidate);
    }
}

