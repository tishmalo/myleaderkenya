<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Models\Candidate;
use App\Models\PublicPulseJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PublicPulseJobRepository implements PublicPulseJobRepositoryInterface
{
    public function create(array $attributes): PublicPulseJob
    {
        return PublicPulseJob::create($attributes);
    }

    public function find(int $id): ?PublicPulseJob
    {
        return PublicPulseJob::query()->with(['candidate', 'submitter'])->find($id);
    }

    public function findByJobRef(string $jobRef): ?PublicPulseJob
    {
        return PublicPulseJob::query()->where('job_ref', $jobRef)->first();
    }

    public function findByEngineJobId(string $engineJobId): ?PublicPulseJob
    {
        return PublicPulseJob::query()->where('engine_job_id', $engineJobId)->first();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return PublicPulseJob::query()
            ->with(['candidate:id,name,nick_name', 'submitter:id,name'])
            ->when($filters['candidate_id'] ?? null, fn ($query, $id) => $query->where('candidate_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
    public function candidateOption(?int $candidateId): ?array
    {
        if (! $candidateId) {
            return null;
        }

        $candidate = Candidate::query()
            ->select(['id', 'name', 'nick_name'])
            ->find($candidateId);

        return $candidate ? [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'nickname' => $candidate->nick_name,
            'image_url' => null,
            'position' => null,
            'party' => null,
            'jurisdiction' => null,
        ] : null;
    }

    public function dueForSync(int $limit = 50): Collection
    {
        return PublicPulseJob::query()
            ->whereNotIn('status', PublicPulseJob::TERMINAL_STATUSES)
            ->whereNotNull('engine_job_id')
            ->where(fn ($query) => $query->whereNull('last_synced_at')->orWhere('last_synced_at', '<=', now()->subMinutes(4)))
            ->oldest('last_synced_at')
            ->limit($limit)
            ->get();
    }

    public function update(PublicPulseJob $job, array $attributes): PublicPulseJob
    {
        $job->forceFill($attributes)->save();

        return $job->refresh();
    }

    public function updateNonTerminal(PublicPulseJob $job, array $attributes): bool
    {
        $updated = PublicPulseJob::query()
            ->whereKey($job->getKey())
            ->whereNotIn('status', PublicPulseJob::TERMINAL_STATUSES)
            ->update($attributes + ['updated_at' => now()]);

        if ($updated) {
            $job->refresh();
        }

        return $updated === 1;
    }
}
