<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Contracts\Repositories\Admin\KittyTypeRepositoryInterface;
use App\Contracts\Repositories\Web\UserTokenRepositoryInterface;
use App\Models\CampaignToolRequest;
use App\Models\CandidateClaimRequest;
use App\Models\CandidateTokenPackage;
use App\Models\User;
use App\Models\UserTokenPurchase;
use App\Models\UserTokenTransaction;
use App\Services\Api\IpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DonorToolboxService
{
    public function __construct(
        private UserTokenRepositoryInterface $tokens,
        private CandidateTokenPackageRepositoryInterface $packages,
        private KittyTypeRepositoryInterface $kittyTypes,
        private IpayService $ipay
    ) {}

    public function data(User $user): array
    {
        return [
            'wallet' => $this->tokens->wallet($user),
            'packages' => $this->packages->active(),
            'purchases' => $this->tokens->purchases($user),
            'transactions' => $this->tokens->transactions($user),
            'adoptions' => $this->tokens->adoptionRequests($user),
            'kittyTypes' => $this->kittyTypes->active(),
        ];
    }

    public function startPurchase(User $user, CandidateTokenPackage $package, array $contact): string
    {
        $this->ipay->assertConfigured();
        $kittyType = $this->kittyTypes->findActive((int) $contact['kitty_type_id']);
        $purchase = $this->tokens->createPurchase([
            'user_id' => $user->id,
            'purchaser_name' => $contact['name'],
            'objective' => 'my_kitty',
            'kitty_type' => $kittyType->slug,
            'kitty_type_id' => $kittyType->id,
            'candidate_token_package_id' => $package->id,
            'provider' => 'ipay',
            'checkout_reference' => $this->uniqueReference(),
            'package_name' => $package->name,
            'token_amount' => $package->token_amount,
            'price' => $package->price,
            'currency' => $package->currency,
            'status' => 'pending',
        ]);
        return $this->ipay->userCheckoutUrl($purchase, $user, $package, $contact);
    }

    public function completePurchase(array $callback): array
    {
        $reference = $this->ipay->callbackReference($callback);
        if (! $reference) return ['status'=>'failed','message'=>'The payment callback had no order reference.'];
        $verification = $this->ipay->verifyTransaction($reference);

        return DB::transaction(function () use ($reference, $verification, $callback): array {
            $purchase = $this->tokens->lockedPurchaseByReference($reference);
            if (! $purchase) return ['status'=>'failed','message'=>'Toolbox purchase not found.'];
            $gateway = ['gateway_transaction_code'=>$verification['transaction_code'],'gateway_status'=>$verification['status'],'gateway_response'=>['callback'=>$callback,'verification'=>$verification['raw']],'callback_received_at'=>now()];
            if ($purchase->status === 'credited') { $this->tokens->updatePurchase($purchase,$gateway); return ['status'=>'success','message'=>'This purchase was already credited.']; }
            if ($verification['status'] !== 'success') { $status=$verification['status']==='pending'?'pending':'failed'; $this->tokens->updatePurchase($purchase,$gateway+['status'=>$status]); return ['status'=>$status,'message'=>'The payment was not confirmed.']; }
            if (abs((float)$verification['amount']-(float)$purchase->price) >= 0.01) { $this->tokens->updatePurchase($purchase,$gateway+['status'=>'failed']); return ['status'=>'failed','message'=>'The confirmed amount does not match the package price.']; }
            $wallet = $this->tokens->lockedWallet($purchase->user_id);
            $before = $wallet->balance;
            $wallet->update(['balance'=>$before+$purchase->token_amount]);
            $this->tokens->createTransaction(['user_token_wallet_id'=>$wallet->id,'user_id'=>$purchase->user_id,'user_token_purchase_id'=>$purchase->id,'type'=>'purchase','status'=>'completed','action_key'=>'toolbox-purchase','action_label'=>'Toolbox token purchase: '.$purchase->package_name,'amount'=>$purchase->token_amount,'balance_before'=>$before,'balance_after'=>$wallet->balance,'metadata'=>['checkout_reference'=>$reference,'price'=>$purchase->price,'currency'=>$purchase->currency],'finalized_at'=>now()]);
            $this->tokens->updatePurchase($purchase,$gateway+['payment_reference'=>$verification['transaction_code']?:$reference,'status'=>'credited','credited_at'=>now()]);
            return ['status'=>'success','message'=>number_format($purchase->token_amount).' tokens credited to your Toolbox.'];
        });
    }

    public function payAdoption(User $user, int $requestId, int $amount): UserTokenTransaction
    {
        return DB::transaction(function () use ($user, $requestId, $amount): UserTokenTransaction {
            $request = $this->tokens->lockedPayableAdoption($user, $requestId);
            if ($request->fulfilment_type !== 'sms_sponsorship') throw ValidationException::withMessages(['payment'=>'Only Bulk SMS sponsorships use Toolbox tokens.']);
            if ($request->status === 'cancelled') throw ValidationException::withMessages(['payment'=>'This sponsorship request was cancelled.']);
            if ($request->payment_status === 'paid') throw ValidationException::withMessages(['payment'=>'This sponsorship is already paid.']);
            if ($request->payment_status === 'refunded') throw ValidationException::withMessages(['payment'=>'This sponsorship was refunded and cannot be paid again.']);
            if ($amount < 1) throw ValidationException::withMessages(['token_amount'=>'Enter at least one token to sponsor.']);
            $wallet = $this->tokens->lockedWallet($user->id);
            if ($wallet->balance < $amount) throw ValidationException::withMessages(['payment'=>'Insufficient Toolbox tokens. Required: '.number_format($amount).', available: '.number_format($wallet->balance).'.']);
            $before=$wallet->balance; $wallet->update(['balance'=>$before-$amount]);
            $correlation=(string)Str::uuid();
            $transaction=$this->tokens->createTransaction(['user_token_wallet_id'=>$wallet->id,'user_id'=>$user->id,'candidate_id'=>$request->candidate_id,'tokenable_type'=>$request::class,'tokenable_id'=>$request->id,'type'=>'sponsorship','status'=>'completed','action_key'=>'bulk-sms-sponsorship','action_label'=>'Sponsor Bulk SMS for '.($request->candidate?->name ?? 'aspirant'),'amount'=>-$amount,'balance_before'=>$before,'balance_after'=>$wallet->balance,'metadata'=>['campaign_tool_request_id'=>$request->id,'correlation_id'=>$correlation],'finalized_at'=>now()]);
            $candidateWallet=$this->tokens->lockedCandidateWallet($request->candidate_id); $candidateBefore=$candidateWallet->balance;
            $candidateWallet->update(['balance'=>$candidateBefore+$amount]);
            $this->tokens->createCandidateTransaction(['candidate_id'=>$request->candidate_id,'candidate_token_wallet_id'=>$candidateWallet->id,'user_id'=>$user->id,
                'tokenable_type'=>$request::class,'tokenable_id'=>$request->id,'type'=>'credit','status'=>'completed','action_key'=>'bulk-sms-sponsorship',
                'action_label'=>'Bulk SMS tokens sponsored by adopter','calculation_type'=>'fixed','quantity'=>1,'unit_tokens'=>$amount,'amount'=>$amount,
                'balance_before'=>$candidateBefore,'balance_after'=>$candidateWallet->balance,'metadata'=>['correlation_id'=>$correlation,'donor_transaction_id'=>$transaction->id],'finalized_at'=>now()]);
            $this->tokens->updateAdoption($request,['tokens_required'=>$amount,'payment_status'=>'paid','user_token_transaction_id'=>$transaction->id,'paid_at'=>now()]);
            return $transaction;
        });
    }

    public function assertClaimPaid(CandidateClaimRequest $claim, User $user): void
    {
        // Adoption approval is independent from funding and fulfilment.
        return;
    }

    public function refundClaim(CandidateClaimRequest $claim, string $reason): void
    {
        if ($claim->relationship !== 'adopter' || ! $claim->user_id) return;
        DB::transaction(function () use ($claim, $reason): void {
            foreach ($this->tokens->paidAdoptionsForClaim($claim->user_id, $claim->candidate_id) as $request) {
                $this->refundPaidAdoption($request, $reason);
            }
        });
    }

    public function refundAdoption(CampaignToolRequest $request, string $reason): void
    {
        if ($request->request_type !== 'adoption') return;
        DB::transaction(function () use ($request, $reason): void {
            $lockedRequest = $this->tokens->lockedAdoption($request->id);
            $this->refundPaidAdoption($lockedRequest, $reason);
        });
    }

    private function refundPaidAdoption(CampaignToolRequest $request, string $reason): void
    {
        if ($request->payment_status !== 'paid' || ! $request->user_id) return;
        $candidateWallet=$this->tokens->lockedCandidateWallet($request->candidate_id);
        $amount=min((int)$request->tokens_required,(int)$candidateWallet->balance);
        if ($amount < 1) { $this->tokens->updateAdoption($request,['payment_status'=>'partially_refunded','refunded_at'=>now()]); return; }
        $candidateBefore=$candidateWallet->balance; $candidateWallet->update(['balance'=>$candidateBefore-$amount]);
        $wallet=$this->tokens->lockedWallet($request->user_id); $before=$wallet->balance; $wallet->update(['balance'=>$before+$amount]);
        $this->tokens->createTransaction(['user_token_wallet_id'=>$wallet->id,'user_id'=>$request->user_id,'candidate_id'=>$request->candidate_id,'tokenable_type'=>$request::class,'tokenable_id'=>$request->id,'type'=>'refund','status'=>'completed','action_key'=>'aspirant-adoption-refund','action_label'=>'Refund aspirant sponsorship','amount'=>$amount,'balance_before'=>$before,'balance_after'=>$wallet->balance,'metadata'=>['reason'=>$reason,'original_transaction_id'=>$request->user_token_transaction_id],'finalized_at'=>now()]);
        $this->tokens->createCandidateTransaction(['candidate_id'=>$request->candidate_id,'candidate_token_wallet_id'=>$candidateWallet->id,'user_id'=>$request->user_id,
            'tokenable_type'=>$request::class,'tokenable_id'=>$request->id,'type'=>'refund','status'=>'completed','action_key'=>'bulk-sms-sponsorship-refund',
            'action_label'=>'Return unspent sponsored Bulk SMS tokens','calculation_type'=>'fixed','quantity'=>1,'unit_tokens'=>$amount,'amount'=>-$amount,
            'balance_before'=>$candidateBefore,'balance_after'=>$candidateWallet->balance,'metadata'=>['reason'=>$reason],'finalized_at'=>now()]);
        $this->tokens->updateAdoption($request,['payment_status'=>$amount===(int)$request->tokens_required?'refunded':'partially_refunded','refunded_at'=>now()]);
    }

    private function uniqueReference(): string
    {
        do { $reference='BOX'.now()->format('ymdHis').Str::upper(Str::random(8)); } while ($this->tokens->findPurchaseByReference($reference));
        return substr($reference,0,30);
    }
}
