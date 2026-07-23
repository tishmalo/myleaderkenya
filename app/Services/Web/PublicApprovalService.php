<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PublicApprovalRepositoryInterface;
use App\Models\Candidate;
use App\Support\HomepageCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicApprovalService
{
    private const PROFILE_MAP = [
        'kalonzo musyoka' => 'kalonzo',
    ];

    public function __construct(
        private PublicApprovalRepositoryInterface $approvalRepository
    ) {}

    public function presidentialCards(): array
    {
        return Cache::remember(
            HomepageCache::key('public-approval-presidential'),
            HomepageCache::ttl(),
            fn (): array => $this->buildPresidentialCards()
        );
    }

    public function presidentialScores(): array
    {
        return collect($this->presidentialCards())
            ->map(fn (array $card): array => [
                'candidate_id' => $card['candidate_id'],
                'approval' => $card['approval'],
            ])
            ->values()
            ->all();
    }

    private function buildPresidentialCards(): array
    {
        return $this->presidentialCandidates()
            ->map(function (Candidate $candidate): ?array {
                $profileSlug = $this->profileSlugForCandidate($candidate);

                if (! $profileSlug) {
                    return null;
                }

                $approval = $this->approvalRepository->approvalForProfile($profileSlug);

                if ($approval === null) {
                    return null;
                }

                $approval = round($approval, 1);
                $isPositive = $approval >= 50;

                return [
                    'candidate_id' => $candidate->id,
                    'name' => $candidate->name,
                    'portrait_url' => $this->portraitUrl($candidate),
                    'approval' => $approval,
                    'direction' => $isPositive ? 'up' : 'down',
                    'theme' => $isPositive ? 'positive' : 'negative',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function presidentialCandidates()
    {
        return Candidate::query()
            ->with(['position'])
            ->when(Schema::hasColumn('candidates', 'approval_status'), fn ($query) => $query->where('approval_status', 'approved'))
            ->whereHas('position', function ($positionQuery): void {
                $positionQuery->whereRaw('LOWER(name) LIKE ?', ['%president%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%presidential%']);
            })
            ->whereNotNull('profile_picture')
            ->where('profile_picture', '!=', '')
            ->latest('created_at')
            ->take(8)
            ->get();
    }

    private function profileSlugForCandidate(Candidate $candidate): ?string
    {
        $name = Str::lower(trim((string) $candidate->name));

        foreach (self::PROFILE_MAP as $needle => $profileSlug) {
            if (str_contains($name, $needle)) {
                return $profileSlug;
            }
        }

        return null;
    }

    private function portraitUrl(Candidate $candidate): ?string
    {
        if (! $candidate->profile_picture) {
            return null;
        }

        if (Str::startsWith($candidate->profile_picture, ['http://', 'https://'])) {
            return $candidate->profile_picture;
        }

        return asset(Storage::url(ltrim($candidate->profile_picture, '/')));
    }
}
