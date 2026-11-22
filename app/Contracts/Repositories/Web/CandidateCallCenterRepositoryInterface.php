<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CandidateCallLog;
use App\Models\CandidateCallScript;
use Illuminate\Support\Collection;

interface CandidateCallCenterRepositoryInterface
{
    public function scriptFor(int $candidateId): ?CandidateCallScript;

    public function saveScript(int $candidateId, array $attributes): CandidateCallScript;

    public function logsFor(int $candidateId, int $limit): Collection;

    public function createLog(array $data): CandidateCallLog;
}
