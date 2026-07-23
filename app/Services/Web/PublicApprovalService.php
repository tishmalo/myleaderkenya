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
            HomepageCache::key('public-approval-presidential-v7'),
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
            ->unique(fn (Candidate $candidate): string => $this->candidateIdentityKey($candidate))
            ->map(function (Candidate $candidate): ?array {
                $approval = $this->approvalForCandidate($candidate);

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
            ->take(5)
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
            ->orderByDesc('featured')
            ->latest('created_at')
            ->get();
    }

    private function approvalForCandidate(Candidate $candidate): ?float
    {
        foreach ($this->profileSlugsForCandidate($candidate) as $profileSlug) {
            $approval = $this->approvalRepository->approvalForProfile($profileSlug);

            if ($approval !== null) {
                return $approval;
            }
        }

        return null;
    }

    private function profileSlugsForCandidate(Candidate $candidate): array
    {
        $names = collect([$candidate->name, $candidate->nick_name])
            ->map(fn ($name): string => $this->cleanCandidateName($name))
            ->filter()
            ->unique();

        return $names
            ->flatMap(fn (string $name): array => $this->slugVariantsForName($name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function slugVariantsForName(string $name): array
    {
        $words = collect(preg_split('/\s+/', $name) ?: [])
            ->map(fn (string $word): string => Str::slug($word))
            ->filter()
            ->values();

        return [
            Str::slug($name),
            $words->last(),
            $words->first(),
        ];
    }

    private function cleanCandidateName($name): string
    {
        return Str::of((string) $name)
            ->lower()
            ->replaceMatches('/\b(dr|hon|honourable|prof|mr|mrs|ms|h\.e)\.?\b/u', '')
            ->trim()
            ->value();
    }

    private function candidateIdentityKey(Candidate $candidate): string
    {
        $source = $this->cleanCandidateName($candidate->name ?: $candidate->nick_name ?: $candidate->id);
        $key = Str::slug($source);

        return $key !== '' ? $key : (string) $candidate->id;
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
