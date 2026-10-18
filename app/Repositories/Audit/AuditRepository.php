<?php

namespace App\Repositories\Audit;

use App\Contracts\Repositories\Audit\AuditRepositoryInterface;
use App\Models\Audit;
use App\Models\Candidate;
use App\Models\ResourceLink;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AuditRepository implements AuditRepositoryInterface
{
    public function paginate(array $filters, ?int $candidateId = null): LengthAwarePaginator
    {
        return Audit::query()->with(['user', 'candidate'])
            ->when($candidateId !== null, fn ($q) => $q->where('candidate_id', $candidateId))
            ->when($filters['event'] ?? null, fn ($q, $v) => $q->where('event', $v))
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['actor_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['actor'] ?? null, function ($q, $value) {
                $q->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$value.'%'));
            })
            ->when($filters['activity'] ?? null, function ($q, $value) {
                $q->where(function ($activity) use ($value) {
                    $activity->where('summary', 'like', '%'.$value.'%')
                        ->orWhere('event', 'like', '%'.$value.'%');
                });
            })
            ->when($filters['candidate_id'] ?? null, fn ($q, $v) => $q->where('candidate_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')->paginate(30)->withQueryString();
    }

    public function candidateCreatorSummary(array $filters = []): Collection
    {
        $candidateMorph = (new Candidate())->getMorphClass();
        $userMorph = (new User())->getMorphClass();

        $summary = Audit::query()
            ->leftJoin('users', function ($join) use ($userMorph) {
                $join->on('users.id', '=', 'audits.user_id')
                    ->where('audits.user_type', '=', $userMorph);
            })
            ->leftJoin('candidates', 'candidates.id', '=', 'audits.candidate_id')
            ->where('audits.event', 'created')
            ->where('audits.auditable_type', $candidateMorph)
            ->whereNotNull('audits.user_id')
            ->whereNotNull('audits.candidate_id')
            ->when($filters['date_from'] ?? null, fn ($q, $value) => $q->whereDate('audits.created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($q, $value) => $q->whereDate('audits.created_at', '<=', $value))
            ->groupBy('audits.user_id', 'users.name')
            ->selectRaw('audits.user_id, users.name as actor_name')
            ->selectRaw('COUNT(DISTINCT CASE WHEN candidates.id IS NOT NULL THEN audits.candidate_id END) as live_candidates')
            ->selectRaw('COUNT(DISTINCT CASE WHEN candidates.id IS NULL THEN audits.candidate_id END) as deleted_candidates')
            ->orderByDesc('live_candidates')
            ->orderBy('actor_name')
            ->get();

        $linksAdded = $this->linksCreatedByActor($filters);

        return $summary->each(function ($contributor) use ($linksAdded): void {
            $contributor->links_added = $linksAdded[(int) $contributor->user_id] ?? 0;
        });
    }

    /**
     * How many links each actor added, counted from the trail so a link that was
     * later removed still credits the person who created it.
     *
     * @return array<int, int>
     */
    private function linksCreatedByActor(array $filters): array
    {
        return Audit::query()
            ->where('event', 'created')
            ->where('auditable_type', (new ResourceLink())->getMorphClass())
            ->whereNotNull('user_id')
            ->when($filters['date_from'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '<=', $value))
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as links_added')
            ->get()
            ->mapWithKeys(fn ($row): array => [(int) $row->user_id => (int) $row->links_added])
            ->all();
    }

    public function findForCandidate(int $id, ?int $candidateId = null): ?Audit
    {
        return Audit::query()->with(['user', 'candidate'])->when($candidateId !== null, fn ($q) => $q->where('candidate_id', $candidateId))->find($id);
    }

    public function create(array $attributes): Audit
    {
        return Audit::query()->create($attributes);
    }
}
