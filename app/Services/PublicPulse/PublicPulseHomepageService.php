<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseHomepageRepositoryInterface;
use App\Models\Candidate;
use App\Models\PublicPulseJob;
use App\Support\HomepageCache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicPulseHomepageService
{
    public function __construct(private PublicPulseHomepageRepositoryInterface $repository) {}

    public function configuration(): array
    {
        $candidates = $this->repository->presidentialCandidates();
        $selections = $this->repository->selections();
        $jobs = $this->repository->latestCompletedJobs($candidates->pluck('id')->all());

        return $candidates->map(function (Candidate $candidate) use ($selections, $jobs): array {
            $selection = $selections->get($candidate->id);
            $job = $jobs->get($candidate->id);

            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'selected' => $selection !== null,
                'sort_order' => $selection?->sort_order ?? 0,
                'has_result' => $job instanceof PublicPulseJob,
                'pulse_score' => $job ? data_get($job->summary, 'pulse_score') : null,
                'completed_at' => $job?->completed_at,
            ];
        })->all();
    }

    public function configure(array $candidateIds, array $orders): void
    {
        $allowed = $this->repository->presidentialCandidates()->pluck('id')->map(fn ($id) => (int) $id);
        $selected = collect($candidateIds)->map(fn ($id) => (int) $id)->unique()->intersect($allowed)->take(5)->values();
        $now = now();

        $rows = $selected->map(fn (int $id, int $index): array => [
            'candidate_id' => $id,
            'sort_order' => max(0, (int) ($orders[$id] ?? $index + 1)),
            'created_at' => $now,
            'updated_at' => $now,
        ])->sortBy('sort_order')->values()->all();

        $this->repository->replaceSelections($rows);
        HomepageCache::flush();
    }

    public function cards(): array
    {
        $selections = $this->repository->selections();
        $candidateIds = $selections->keys()->map(fn ($id) => (int) $id)->all();
        if ($candidateIds === []) {
            return [];
        }

        $candidates = $this->repository->presidentialCandidates()->keyBy('id');
        $jobs = $this->repository->latestCompletedJobs($candidateIds);

        return $selections->map(function ($selection) use ($candidates, $jobs): ?array {
            $candidate = $candidates->get($selection->candidate_id);
            $job = $jobs->get($selection->candidate_id);
            if (! $candidate instanceof Candidate || ! $job instanceof PublicPulseJob) {
                return null;
            }

            $score = round((float) data_get($job->summary, 'pulse_score', 0), 1);

            return [
                'candidate_id' => $candidate->id,
                'name' => $candidate->name,
                'portrait_url' => $this->portraitUrl($candidate),
                'approval' => $score,
                'direction' => $score >= 0 ? 'up' : 'down',
                'theme' => $score >= 0 ? 'positive' : 'negative',
                'confidence' => data_get($job->summary, 'overall_confidence', 'low'),
                'completed_at' => $job->completed_at,
            ];
        })->filter()->take(5)->values()->all();
    }

    private function portraitUrl(Candidate $candidate): ?string
    {
        if (! $candidate->profile_picture) {
            return null;
        }

        return Str::startsWith($candidate->profile_picture, ['http://', 'https://'])
            ? $candidate->profile_picture
            : asset(Storage::url(ltrim($candidate->profile_picture, '/')));
    }
}
