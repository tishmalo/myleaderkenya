<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use App\Services\Admin\CandidateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AspirantApiService
{
    private const APPROVED_UPDATE_MESSAGE = 'Approved aspirants cannot be updated through the public API. Ask an admin to make changes.';

    public function __construct(
        private CandidateService $candidates,
        private AspirantWorkspaceService $workspace,
        private UserRepositoryInterface $users,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, UploadedFile|null>  $files
     * @return array{user: User, candidate: Candidate, token: string}
     */
    public function register(array $validated, array $files): array
    {
        $phone = $validated['phone_1'] ?? $validated['phone'] ?? null;
        $email = $validated['email_1'] ?? $validated['email'] ?? null;
        $username = $this->uniqueUsername($validated['username'] ?? null, (string) $validated['name']);

        [$user, $candidate] = DB::transaction(function () use ($validated, $files, $phone, $email, $username): array {
            $user = $this->users->create([
                'name' => $validated['name'],
                'username' => $username,
                'email' => $email ?: $username . '@regista.local',
                'password' => $validated['password'],
                'role' => 'user',
                'phone' => $phone,
                'county' => $validated['county'] ?? null,
                'constituency' => $validated['constituency'] ?? null,
                'ward' => $validated['ward'] ?? null,
                'country_of_residence' => 'Kenya',
                'is_aspirant' => true,
                'relationship' => 'aspirant',
            ]);

            $candidate = $this->candidates->createCandidate(
                [
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'nick_name' => $validated['nick_name'] ?? null,
                    'phone' => $phone,
                    'phone_1' => $phone,
                    'phone_2' => $validated['phone_2'] ?? null,
                    'email' => $email,
                    'email_1' => $email,
                    'email_2' => $validated['email_2'] ?? null,
                    'position_id' => $validated['position_id'],
                    'political_party_id' => $validated['political_party_id'] ?? $validated['party'] ?? null,
                    'about' => $validated['about'] ?? null,
                    'county' => $validated['county'] ?? null,
                    'constituency' => $validated['constituency'] ?? null,
                    'ward' => $validated['ward'] ?? null,
                    'approval_status' => 'pending',
                ],
                $files['profile_picture'] ?? $files['profile_pic'] ?? null,
                $files['cover_photo'] ?? null,
                $files['campaign_poster'] ?? null,
                $files['campaign_video'] ?? null,
                $files['campaign_skiza_audio'] ?? null
            );

            if (Schema::hasTable('candidate_user_relationships')) {
                $user->relatedCandidates()->syncWithoutDetaching([
                    $candidate->id => ['relationship' => 'aspirant'],
                ]);
            }

            return [$user, $candidate];
        });

        $candidate->load(['position', 'politicalParty']);

        $user->tokens()->delete();
        $token = $user->createToken('aspirant-app-token')->plainTextToken;

        return ['user' => $user, 'candidate' => $candidate, 'token' => $token];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, UploadedFile|null>  $files
     */
    public function update(Candidate $candidate, array $validated, array $files): Candidate
    {
        if (Schema::hasColumn('candidates', 'approval_status') && $candidate->approval_status === 'approved') {
            abort(403, self::APPROVED_UPDATE_MESSAGE);
        }

        $data = $this->updateData($validated);

        if (Schema::hasColumn('candidates', 'approval_status')) {
            $data['approval_status'] = 'pending';
        }

        $this->candidates->updateCandidate(
            $candidate,
            $data,
            $files['profile_picture'] ?? $files['profile_pic'] ?? null,
            $files['cover_photo'] ?? null,
            $files['campaign_poster'] ?? null,
            $files['campaign_video'] ?? null,
            $files['campaign_skiza_audio'] ?? null
        );

        return $candidate->refresh()->load(['position', 'politicalParty']);
    }

    public function profile(User $user): ?Candidate
    {
        return $this->workspace->candidateForUser($user)?->loadMissing(['position', 'politicalParty']);
    }

    public function publicProfile(Candidate $candidate): Candidate
    {
        if (! $this->candidates->isPubliclyVisible($candidate)) {
            abort(404);
        }

        $candidate->load(['position', 'politicalParty']);

        return $candidate->setRelation('relatedArticles', $this->candidates->publishedArticlesFor($candidate, 10));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function updateData(array $validated): array
    {
        $data = [];

        foreach ([
            'name',
            'nick_name',
            'phone_2',
            'email_2',
            'position_id',
            'about',
            'county',
            'constituency',
            'ward',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            }
        }

        if (array_key_exists('phone_1', $validated) || array_key_exists('phone', $validated)) {
            $phone = $validated['phone_1'] ?? $validated['phone'] ?? null;
            $data['phone'] = $phone;
            $data['phone_1'] = $phone;
        }

        if (array_key_exists('email_1', $validated) || array_key_exists('email', $validated)) {
            $email = $validated['email_1'] ?? $validated['email'] ?? null;
            $data['email'] = $email;
            $data['email_1'] = $email;
        }

        if (array_key_exists('political_party_id', $validated) || array_key_exists('party', $validated)) {
            $data['political_party_id'] = $validated['political_party_id'] ?? $validated['party'] ?? null;
        }

        return $data;
    }

    private function uniqueUsername(?string $preferred, string $name): string
    {
        $base = filled($preferred)
            ? trim((string) $preferred)
            : Str::limit(Str::slug($name, '_'), 40, '');

        if ($base === '') {
            $base = 'aspirant';
        }

        $username = $base;
        $suffix = 1;

        while ($this->users->findByUsername($username)) {
            $username = $base . '_' . $suffix++;
        }

        return $username;
    }
}
