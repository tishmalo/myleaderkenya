<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\CandidateTokenWallet;

interface CandidateTokenWalletRepositoryInterface
{
    public function firstOrCreateForCandidate(Candidate $candidate): CandidateTokenWallet;

    public function findForCandidate(Candidate $candidate): ?CandidateTokenWallet;
}
