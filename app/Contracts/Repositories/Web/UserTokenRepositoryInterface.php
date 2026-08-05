<?php

namespace App\Contracts\Repositories\Web;

use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Models\UserTokenPurchase;
use App\Models\UserTokenTransaction;
use App\Models\UserTokenWallet;
use Illuminate\Support\Collection;

interface UserTokenRepositoryInterface
{
    public function wallet(User $user): UserTokenWallet;
    public function lockedWallet(int $userId): UserTokenWallet;
    public function createPurchase(array $data): UserTokenPurchase;
    public function findPurchaseByReference(string $reference): ?UserTokenPurchase;
    public function lockedPurchaseByReference(string $reference): ?UserTokenPurchase;
    public function updatePurchase(UserTokenPurchase $purchase, array $data): bool;
    public function createTransaction(array $data): UserTokenTransaction;
    public function transactions(User $user, int $limit = 30): Collection;
    public function purchases(User $user, int $limit = 10): Collection;
    public function adoptionRequests(User $user): Collection;
    public function lockedPayableAdoption(User $user, int $requestId): CampaignToolRequest;
    public function lockedAdoption(int $requestId): CampaignToolRequest;
    public function paidAdoptionsForClaim(int $userId, int $candidateId): Collection;
    public function updateAdoption(CampaignToolRequest $request, array $data): bool;
}