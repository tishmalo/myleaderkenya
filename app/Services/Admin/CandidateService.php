<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateSmsSettingRepositoryInterface;
use App\Contracts\Repositories\Web\AspirantSupportRepositoryInterface;
use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\SupportGroupType;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Ward;
use App\Models\PoliticalParty;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CandidateService
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository,
        private CandidateSmsSettingRepositoryInterface $smsSettingRepository,
        private SettingService $settingService,
        private AspirantSupportRepositoryInterface $aspirantSupports
    ) {}

    // -------------------------------------------------------------------------
    // Admin CRUD
    // -------------------------------------------------------------------------

    public function getPaginatedCandidates(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->candidateRepository->paginate($perPage, $filters);
    }

    public function getApprovedAspirantsForApi(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        return $this->candidateRepository->paginateApprovedForApi($filters, $perPage);
    }

    public function getAdminDonationData(Candidate $candidate): array
    {
        return [
            'donations' => $this->aspirantSupports->forCandidateAdmin((int) $candidate->id),
            'donationTotals' => $this->aspirantSupports->adminTotalsForCandidate((int) $candidate->id),
        ];
    }

    public function createCandidate(array $data, ?UploadedFile $picture = null, ?UploadedFile $coverPhoto = null, ?UploadedFile $campaignPoster = null, ?UploadedFile $campaignVideo = null, ?UploadedFile $campaignSkizaAudio = null): Candidate
    {
        $smsSettings = $this->extractSmsSettings($data);
        $supportContacts = $this->extractSupportContacts($data);
        $data = $this->normalizeCandidateData($data);

        $identity = implode('|', [
            Str::lower(trim((string) ($data['name'] ?? ''))),
            (string) ($data['position_id'] ?? ''),
            (string) ($data['political_party_id'] ?? ''),
        ]);
        $lock = Cache::lock('candidate-create:'.hash('sha256', $identity), 15);

        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'name' => 'This aspirant profile is already being submitted. Please wait and search for the existing profile.',
            ]);
        }

        try {
            if ($this->candidateRepository->findPotentialDuplicate($data)) {
                throw ValidationException::withMessages([
                    'name' => 'A matching aspirant profile already exists. Search for and claim the existing profile instead of submitting it again.',
                ]);
            }

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
        } finally {
            $lock->release();
        }
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
    public function updateApprovalStatus(Candidate $candidate, string $status): bool
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected approval status is invalid.',
            ]);
        }

        return $this->candidateRepository->update($candidate, [
            'approval_status' => $status,
        ]);
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

        if (! Schema::hasColumn('candidates', 'approval_status')) {
            unset($data['approval_status']);
        }

        if (! Schema::hasColumn('candidates', 'is_imported')) {
            unset($data['is_imported']);
        }

        if (! Schema::hasColumn('candidates', 'import_status')) {
            unset($data['import_status']);
        }

        if (! Schema::hasColumn('candidates', 'linked_candidate_id')) {
            unset($data['linked_candidate_id']);
        }

        $socialFields = ['facebook_url', 'x_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'whatsapp_group_url'];

        foreach ($socialFields as $field) {
            if (! Schema::hasColumn('candidates', $field)) {
                if (filled($data[$field] ?? null)) {
                    throw ValidationException::withMessages([
                        $field => 'Social media fields are not ready yet. Apply the social links database migration, then save again.',
                    ]);
                }

                unset($data[$field]);
            }
        }
        foreach (['campaign_video_url', 'campaign_song_url'] as $field) {
            if (! Schema::hasColumn('candidates', $field)) {
                if (filled($data[$field] ?? null)) {
                    throw ValidationException::withMessages([
                        $field => 'Campaign media links are not ready yet. Apply the campaign media migration, then save again.',
                    ]);
                }

                unset($data[$field]);
            }
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

    public function getPublicIndex(array $filters, int $perPage = 30): array
    {
        $showPositionGroups = empty($filters['position']);
        $positionGroups = $showPositionGroups
            ? $this->candidateRepository->publicPositionGroups($filters, 20)
                ->filter(fn (array $group): bool => $this->publicPositionOrder($group['position']->name) < 999)
                ->sortBy(fn (array $group): int => $this->publicPositionOrder($group['position']->name))
                ->values()
            : collect();

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

        $countyNavigation = collect();
        if (! $showPositionGroups
            && ! $showLocationGroups
            && ! $showAspirantGroups
            && filled($filters['position'] ?? null)
            && filled($filters['county'] ?? null)) {
            $countyNavigation = $this->candidateRepository->publicAlternativeCountyGroups(
                $filters,
                (string) $filters['county']
            );
        }

        return [
            'candidates' => $showPositionGroups || $showLocationGroups || $showAspirantGroups
                ? new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, $perPage)
                : $this->candidateRepository->filterPublic($filters, $perPage),
            'positionGroups' => $positionGroups,
            'showPositionGroups' => $showPositionGroups,
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
            'countyNavigation' => $countyNavigation,
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

    private function publicPositionOrder(string $position): int
    {
        $key = strtolower(str_replace(['_', '-'], ' ', trim($position)));
        $key = preg_replace('/\s+/', ' ', $key);

        return match ($key) {
            'president', 'presidential' => 10,
            'governor' => 20,
            'senator' => 30,
            'women rep', 'woman rep', 'women representative', 'woman representative' => 40,
            'mp', 'member of parliament' => 50,
            'mca', 'member of county assembly' => 60,
            default => 999,
        };
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
            return 'All';
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

    /**
     * @return array{imported:int,linked:int,errors:array<int,string>}
     */
    public function importCandidatesFromCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw ValidationException::withMessages([
                'file' => 'Could not read the uploaded file.',
            ]);
        }

        $first = fgets($handle);
        if ($first === false) {
            fclose($handle);
            throw ValidationException::withMessages([
                'file' => 'File is empty.',
            ]);
        }

        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $header = array_map(
            fn ($h) => Str::slug(trim((string) $h), '_'),
            str_getcsv($first)
        );

        $positions = Position::query()
            ->get()
            ->keyBy(fn (Position $p) => Str::lower(trim($p->name)));

        $parties = collect();
        foreach (PoliticalParty::all() as $party) {
            $parties[Str::lower(trim($party->name))] = $party;
            if ($party->abbreviation) {
                $parties[Str::lower(trim($party->abbreviation))] = $party;
            }
        }

        $imported = 0;
        $linked = 0;
        $errors = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = isset($data[$i]) ? trim((string) $data[$i]) : '';
            }

            $name = $row['name'] ?? '';
            $positionLabel = $row['position'] ?? '';
            $positionName = Str::lower($positionLabel);

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: name is required.";
                continue;
            }

            if ($positionName === '' || ! isset($positions[$positionName])) {
                $errors[] = "Row {$rowNumber}: unknown or missing position \"{$positionLabel}\".";
                continue;
            }

            $position = $positions[$positionName];
            $partyId = null;
            $partyRaw = $row['political_party'] ?? '';
            if ($partyRaw !== '') {
                $partyKey = Str::lower($partyRaw);
                if (! isset($parties[$partyKey])) {
                    $errors[] = "Row {$rowNumber}: unknown political party \"{$partyRaw}\".";
                    continue;
                }
                $partyId = $parties[$partyKey]->id;
            }

            $county = ($row['county'] ?? '') !== '' ? $row['county'] : null;
            $constituency = ($row['constituency'] ?? '') !== '' ? $row['constituency'] : null;
            $ward = ($row['ward'] ?? '') !== '' ? $row['ward'] : null;

            $pos = $positionName;
            $needsCounty = str_contains($pos, 'governor')
                || str_contains($pos, 'senator')
                || str_contains($pos, 'women representative')
                || str_contains($pos, 'mp')
                || str_contains($pos, 'member of parliament')
                || str_contains($pos, 'mca')
                || str_contains($pos, 'county assembly');
            $needsConstituency = str_contains($pos, 'mp')
                || str_contains($pos, 'member of parliament')
                || str_contains($pos, 'mca')
                || str_contains($pos, 'county assembly');
            $needsWard = str_contains($pos, 'mca') || str_contains($pos, 'county assembly');

            if ($needsCounty && ! $county) {
                $errors[] = "Row {$rowNumber}: county is required for this position.";
                continue;
            }
            if ($needsConstituency && ! $constituency) {
                $errors[] = "Row {$rowNumber}: constituency is required for this position.";
                continue;
            }
            if ($needsWard && ! $ward) {
                $errors[] = "Row {$rowNumber}: ward is required for this position.";
                continue;
            }

            try {
                $result = $this->createImportedCandidate([
                    'name' => $name,
                    'nick_name' => ($row['nick_name'] ?? '') !== '' ? $row['nick_name'] : null,
                    'phone' => ($row['phone'] ?? '') !== '' ? $row['phone'] : null,
                    'email' => ($row['email'] ?? '') !== '' ? $row['email'] : null,
                    'political_party_id' => $partyId,
                    'position_id' => $position->id,
                    'county' => $county,
                    'constituency' => $constituency,
                    'ward' => $ward,
                    'about' => ($row['about'] ?? '') !== '' ? $row['about'] : null,
                    'approval_status' => 'pending',
                    'is_imported' => true,
                    'import_status' => 'pending',
                ]);

                $imported++;
                if ($result['linked']) {
                    $linked++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return compact('imported', 'linked', 'errors');
    }

    /**
     * Create an imported aspirant without the normal "duplicate exists" rejection.
     * If a same-name profile exists, link to it.
     *
     * @return array{candidate: Candidate, linked: bool}
     */
    public function createImportedCandidate(array $data): array
    {
        $data = $this->normalizeCandidateData($data);

        $name = Str::lower(trim((string) ($data['name'] ?? '')));

        // Prefer a non-imported live profile when linking
        $existing = Candidate::query()
            ->whereRaw('LOWER(name) = ?', [$name])
            ->where(function ($q) {
                $q->whereNull('import_status')
                    ->orWhere('import_status', '!=', 'discarded');
            })
            ->orderByRaw('CASE WHEN COALESCE(is_imported, 0) = 0 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->first();

        if (Schema::hasColumn('candidates', 'linked_candidate_id')) {
            $data['linked_candidate_id'] = $existing?->id;
        }

        if (Schema::hasColumn('candidates', 'is_imported')) {
            $data['is_imported'] = true;
        }

        if (Schema::hasColumn('candidates', 'import_status')) {
            $data['import_status'] = 'pending';
        }

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $data['approval_status'] = $data['approval_status'] ?? 'pending';
        }

        // Do NOT call findPotentialDuplicate — import is reviewable on purpose
        $candidate = $this->candidateRepository->create($data);

        return [
            'candidate' => $candidate,
            'linked' => $existing !== null,
        ];
    }

    public function publishImportedCandidate(Candidate $candidate): Candidate
    {
        if (! ($candidate->is_imported ?? false) || ($candidate->import_status ?? null) !== 'pending') {
            throw ValidationException::withMessages([
                'candidate' => 'Only pending imported aspirants can be published.',
            ]);
        }

        $payload = [
            'import_status' => 'published',
            'approval_status' => 'approved',
        ];

        // Only keep columns that exist
        if (! Schema::hasColumn('candidates', 'import_status')) {
            unset($payload['import_status']);
        }
        if (! Schema::hasColumn('candidates', 'approval_status')) {
            unset($payload['approval_status']);
        }

        $this->candidateRepository->update($candidate, $payload);

        return $candidate->refresh();
    }

    public function discardImportedCandidate(Candidate $candidate): Candidate
    {
        if (! ($candidate->is_imported ?? false)) {
            throw ValidationException::withMessages([
                'candidate' => 'Only imported aspirants can be discarded.',
            ]);
        }

        $payload = [
            'import_status' => 'discarded',
            'approval_status' => 'rejected',
        ];

        if (! Schema::hasColumn('candidates', 'import_status')) {
            unset($payload['import_status']);
        }
        if (! Schema::hasColumn('candidates', 'approval_status')) {
            unset($payload['approval_status']);
        }

        $this->candidateRepository->update($candidate, $payload);

        return $candidate->refresh();
    }

    /**
     * Stream all candidates (matching the given admin filters) into a CSV on the local disk.
     *
     * @return array{path:string,count:int}
     */
    public function exportCandidatesToCsv(array $filters, string $downloadName): array
    {
        $headers = [
            'id', 'name', 'nick_name', 'phone', 'email', 'political_party', 'position',
            'county', 'constituency', 'ward', 'about', 'featured', 'approval_status',
            'is_imported', 'import_status', 'created_at',
        ];

        $disk = Storage::disk('local');
        $relativePath = 'exports/'.Str::slug(pathinfo($downloadName, PATHINFO_FILENAME)).'-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(8)).'.csv';

        $handle = fopen($disk->path($relativePath), 'w');
        if (! $handle) {
            throw new \RuntimeException('Could not open the export file for writing.');
        }

        // UTF-8 BOM so Excel opens accents correctly
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, $headers);

        $count = 0;
        $this->candidateRepository->exportQuery($filters)
            ->with(['position:id,name', 'politicalParty:id,name'])
            ->chunkById(500, function ($candidates) use (&$handle, &$count): void {
                foreach ($candidates as $candidate) {
                    fputcsv($handle, [
                        $candidate->id,
                        $candidate->name,
                        $candidate->nick_name ?? '',
                        $candidate->phone ?? '',
                        $candidate->email ?? '',
                        $candidate->politicalParty?->name ?? '',
                        $candidate->position?->name ?? '',
                        $candidate->county ?? '',
                        $candidate->constituency ?? '',
                        $candidate->ward ?? '',
                        $candidate->about ?? '',
                        $candidate->featured ? '1' : '0',
                        $candidate->approval_status ?? '',
                        $candidate->is_imported ? '1' : '0',
                        $candidate->import_status ?? '',
                        $candidate->created_at?->toDateTimeString() ?? '',
                    ]);
                    $count++;
                }
            });

        fclose($handle);

        return ['path' => $relativePath, 'count' => $count];
    }
}
