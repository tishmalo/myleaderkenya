<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\CandidateTokenWalletRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateTokenWallet;

class CandidateTokenWalletRepository implements CandidateTokenWalletRepositoryInterface
{
    public function firstOrCreateForCandidate(Candidate $candidate): CandidateTokenWallet
    {
        return CandidateTokenWallet::firstOrCreate(['candidate_id' => $candidate->id], ['balance' => 0]);
    }

    public function findForCandidate(Candidate $candidate): ?CandidateTokenWallet
    {
        return CandidateTokenWallet::where('candidate_id', $candidate->id)->first();
    }
}
