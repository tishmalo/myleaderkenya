<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PoliticalPartyTokenRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateTokenTransaction;
use App\Models\CandidateTokenWallet;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyTokenPurchase;
use App\Models\PoliticalPartyTokenTransaction;
use App\Models\PoliticalPartyTokenTransfer;
use App\Models\PoliticalPartyTokenWallet;

class PoliticalPartyTokenRepository implements PoliticalPartyTokenRepositoryInterface
{
    public function wallet(PoliticalParty $party): PoliticalPartyTokenWallet
    {
        return PoliticalPartyTokenWallet::firstOrCreate(
            ['political_party_id' => $party->id],
            ['balance' => 0],
        );
    }

    public function lockedPartyWallet(int $partyId): PoliticalPartyTokenWallet
    {
        PoliticalPartyTokenWallet::firstOrCreate(
            ['political_party_id' => $partyId],
            ['balance' => 0],
        );

        return PoliticalPartyTokenWallet::where('political_party_id', $partyId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function lockedCandidateWallet(Candidate $candidate): CandidateTokenWallet
    {
        CandidateTokenWallet::firstOrCreate(
            ['candidate_id' => $candidate->id],
            ['balance' => 0],
        );

        return CandidateTokenWallet::where('candidate_id', $candidate->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function createPurchase(array $data): PoliticalPartyTokenPurchase
    {
        return PoliticalPartyTokenPurchase::create($data);
    }

    public function lockedPurchaseByReference(
        string $reference,
    ): ?PoliticalPartyTokenPurchase {
        return PoliticalPartyTokenPurchase::where('checkout_reference', $reference)
            ->lockForUpdate()
            ->first();
    }

    public function updatePurchase(
        PoliticalPartyTokenPurchase $purchase,
        array $data,
    ): bool {
        return $purchase->update($data);
    }

    public function createPartyTransaction(
        array $data,
    ): PoliticalPartyTokenTransaction {
        return PoliticalPartyTokenTransaction::create($data);
    }

    public function createCandidateTransaction(
        array $data,
    ): CandidateTokenTransaction {
        return CandidateTokenTransaction::create($data);
    }

    public function createTransfer(array $data): PoliticalPartyTokenTransfer
    {
        return PoliticalPartyTokenTransfer::create($data);
    }

    public function updatePartyWallet(
        PoliticalPartyTokenWallet $wallet,
        int $balance,
    ): bool {
        return $wallet->update(['balance' => $balance]);
    }

    public function updateCandidateWallet(
        CandidateTokenWallet $wallet,
        int $balance,
    ): bool {
        return $wallet->update(['balance' => $balance]);
    }
}
