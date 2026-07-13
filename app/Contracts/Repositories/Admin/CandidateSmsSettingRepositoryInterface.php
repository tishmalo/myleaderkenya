<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\Candidate;
use App\Models\CandidateSmsSetting;

interface CandidateSmsSettingRepositoryInterface
{
    public function findForCandidate(Candidate $candidate): ?CandidateSmsSetting;

    public function upsertForCandidate(Candidate $candidate, array $data): CandidateSmsSetting;
}
