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

    public function __construct(
        private PublicApprovalRepositoryInterface $approvalRepository
    ) {}

    public function presidentialCards(): array
    {
        return Cache::remember(
            HomepageCache::key('public-approval-presidential-v2'),
            HomepageCache::ttl(),
            fn (): array => $this->buildPresidentialCards()
        );
    }

    public function presidentialScores(): array
    {
        return collect($this->presidentialCards())
            ->map(fn (array $card): float => (float) $card['approval'])
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
            ->get();
    }

    private function profileSlugForCandidate(Candidate $candidate): ?string
    {
        $source = trim((string) ($candidate->nick_name ?: $candidate->name));

        if ($source === '') {
            return null;
        }

        $source = Str::of($source)
            ->lower()
            ->replaceMatches('/\b(dr|hon|honourable|prof|mr|mrs|ms|h\.e)\.?\b/u', '')
            ->trim()
            ->value();

        foreach (preg_split('/\s+/', $source) ?: [] as $part) {
            $slug = Str::slug($part);

            if ($slug !== '') {
                return $slug;
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
