<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateCallCenterRepositoryInterface;
use App\Models\CandidateCallLog;
use App\Models\CandidateCallScript;
use Illuminate\Support\Collection;

class CandidateCallCenterRepository implements CandidateCallCenterRepositoryInterface
{
    public function scriptFor(int $candidateId): ?CandidateCallScript
    {
        return CandidateCallScript::where('candidate_id', $candidateId)->first();
    }

    public function saveScript(int $candidateId, array $attributes): CandidateCallScript
    {
        return CandidateCallScript::updateOrCreate(
            ['candidate_id' => $candidateId],
            $attributes
        );
    }

    public function logsFor(int $candidateId, int $limit): Collection
    {
        return CandidateCallLog::with(['caller', 'voter'])
            ->where('candidate_id', $candidateId)
            ->latest('called_at')
            ->take($limit)
            ->get();
    }

    public function createLog(array $data): CandidateCallLog
    {
        return CandidateCallLog::create($data);
    }
}
