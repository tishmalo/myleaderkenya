<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\CandidateSmsSettingRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateSmsSetting;

class CandidateSmsSettingRepository implements CandidateSmsSettingRepositoryInterface
{
    public function findForCandidate(Candidate $candidate): ?CandidateSmsSetting
    {
        return $candidate->smsSetting;
    }

    public function upsertForCandidate(Candidate $candidate, array $data): CandidateSmsSetting
    {
        return CandidateSmsSetting::updateOrCreate(
            ['candidate_id' => $candidate->id],
            $data
        );
    }
}
