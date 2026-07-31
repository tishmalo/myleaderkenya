<?php

namespace App\Contracts\Repositories\Web;

use App\Models\Candidate;
use App\Models\CandidateTokenTransaction;
use App\Models\CandidateTokenWallet;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyTokenPurchase;
use App\Models\PoliticalPartyTokenTransaction;
use App\Models\PoliticalPartyTokenTransfer;
use App\Models\PoliticalPartyTokenWallet;

interface PoliticalPartyTokenRepositoryInterface
{
    public function wallet(PoliticalParty $party): PoliticalPartyTokenWallet;

    public function lockedPartyWallet(int $partyId): PoliticalPartyTokenWallet;

    public function lockedCandidateWallet(Candidate $candidate): CandidateTokenWallet;

    public function createPurchase(array $data): PoliticalPartyTokenPurchase;

    public function lockedPurchaseByReference(string $reference): ?PoliticalPartyTokenPurchase;

    public function updatePurchase(PoliticalPartyTokenPurchase $purchase, array $data): bool;

    public function createPartyTransaction(array $data): PoliticalPartyTokenTransaction;

    public function createCandidateTransaction(array $data): CandidateTokenTransaction;

    public function createTransfer(array $data): PoliticalPartyTokenTransfer;

    public function updatePartyWallet(PoliticalPartyTokenWallet $wallet, int $balance): bool;

    public function updateCandidateWallet(CandidateTokenWallet $wallet, int $balance): bool;
}
