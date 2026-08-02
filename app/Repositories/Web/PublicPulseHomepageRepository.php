<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicPulseHomepageRepositoryInterface;
use App\Models\Candidate;
use App\Models\PublicPulseHomepageCandidate;
use App\Models\PublicPulseJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicPulseHomepageRepository implements PublicPulseHomepageRepositoryInterface
{
    public function presidentialCandidates(): Collection
    {
        return Candidate::query()
            ->with(['position:id,name'])
            ->when(Schema::hasColumn('candidates', 'approval_status'), fn ($query) => $query->where('approval_status', 'approved'))
            ->whereHas('position', fn ($query) => $query
                ->whereRaw('LOWER(name) LIKE ?', ['%president%'])
                ->orWhereRaw('LOWER(name) LIKE ?', ['%presidential%']))
            ->orderBy('name')
            ->get();
    }

    public function selections(): Collection
    {
        return PublicPulseHomepageCandidate::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('candidate_id');
    }

    public function latestCompletedJobs(array $candidateIds): Collection
    {
        return PublicPulseJob::query()
            ->whereIn('candidate_id', $candidateIds)
            ->where('status', PublicPulseJob::STATUS_COMPLETED)
            ->whereNotNull('summary')
            ->latest('completed_at')
            ->latest('id')
            ->get()
            ->unique('candidate_id')
            ->keyBy('candidate_id');
    }

    public function replaceSelections(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            PublicPulseHomepageCandidate::query()->delete();
            if ($rows !== []) {
                PublicPulseHomepageCandidate::query()->insert($rows);
            }
        });
    }
}
