<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\UserTokenRepositoryInterface;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Models\UserTokenPurchase;
use App\Models\UserTokenTransaction;
use App\Models\UserTokenWallet;
use Illuminate\Support\Collection;

class UserTokenRepository implements UserTokenRepositoryInterface
{
    public function wallet(User $user): UserTokenWallet
    {
        return UserTokenWallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }

    public function lockedWallet(int $userId): UserTokenWallet
    {
        UserTokenWallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        return UserTokenWallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }

    public function createPurchase(array $data): UserTokenPurchase { return UserTokenPurchase::create($data); }
    public function findPurchaseByReference(string $reference): ?UserTokenPurchase { return UserTokenPurchase::where('checkout_reference', $reference)->first(); }
    public function lockedPurchaseByReference(string $reference): ?UserTokenPurchase { return UserTokenPurchase::where('checkout_reference', $reference)->lockForUpdate()->first(); }
    public function updatePurchase(UserTokenPurchase $purchase, array $data): bool { return $purchase->update($data); }
    public function createTransaction(array $data): UserTokenTransaction { return UserTokenTransaction::create($data); }
    public function transactions(User $user, int $limit = 30): Collection { return UserTokenTransaction::with('candidate:id,name')->where('user_id', $user->id)->latest()->limit($limit)->get(); }
    public function purchases(User $user, int $limit = 10): Collection { return UserTokenPurchase::where('user_id', $user->id)->latest()->limit($limit)->get(); }
    public function adoptionRequests(User $user): Collection
    {
        return CampaignToolRequest::with(['candidate:id,name','selectedTools:id,title'])
            ->where('request_type', 'adoption')->where('user_id', $user->id)->latest()->get();
    }
    public function lockedPayableAdoption(User $user, int $requestId): CampaignToolRequest
    {
        return CampaignToolRequest::where('request_type', 'adoption')->where('user_id', $user->id)->whereKey($requestId)->lockForUpdate()->firstOrFail();
    }
    public function lockedAdoption(int $requestId): CampaignToolRequest
    {
        return CampaignToolRequest::where('request_type', 'adoption')->whereKey($requestId)->lockForUpdate()->firstOrFail();
    }
    public function paidAdoptionsForClaim(int $userId, int $candidateId): Collection
    {
        return CampaignToolRequest::where('request_type','adoption')->where('user_id',$userId)->where('candidate_id',$candidateId)->where('payment_status','paid')->lockForUpdate()->get();
    }
    public function updateAdoption(CampaignToolRequest $request, array $data): bool { return $request->update($data); }
}