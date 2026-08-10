<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CampaignToolCommerceRepositoryInterface;
use App\Models\CampaignToolPayment;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Contracts\Repositories\Web\UserTokenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignToolCommerceService
{
    public function __construct(private CampaignToolCommerceRepositoryInterface $commerce, private UserTokenRepositoryInterface $tokens) {}

    public function redeemPackage(User $user, CampaignToolRequest $request, int $packageId): void
    {
        abort_unless($request->user_id === $user->id && $request->fulfilment_type === 'paid_package', 404);
        abort_if($request->status === 'cancelled', 422, 'This sponsorship request was cancelled.');
        $request->loadMissing('payment');
        if ($request->payment && in_array($request->payment->status,['funded','fulfilled'],true)) throw ValidationException::withMessages(['payment'=>'This package is already paid.']);
        DB::transaction(function () use ($request,$user,$packageId): void {
            $lockedRequest=CampaignToolRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($lockedRequest->payment()->whereIn('status',['funded','fulfilled'])->exists()) throw ValidationException::withMessages(['payment'=>'This package is already funded.']);
            $package=$this->commerce->findActivePackage($packageId,(int)$lockedRequest->campaign_tool_id);
            $rate=max(0,min(100,(float)config('campaign_tools.platform_commission_percent',20)));
            $gross=(int)$package->token_cost; $platform=(int)round($gross*$rate/100); $fulfilment=$gross-$platform;
            $wallet=$this->tokens->lockedWallet($user->id);
            if ($wallet->balance < $gross) throw ValidationException::withMessages(['payment'=>'Insufficient Toolbox tokens. Required: '.number_format($gross).', available: '.number_format($wallet->balance).'.']);
            $before=$wallet->balance; $wallet->update(['balance'=>$before-$gross]);
            $lockedRequest->update(['campaign_tool_package_id'=>$package->id]);
            $payment=$this->commerce->createPayment(['campaign_tool_request_id'=>$request->id,'campaign_tool_package_id'=>$package->id,
                'user_id'=>$user->id,'candidate_id'=>$request->candidate_id,'provider'=>'toolbox','checkout_reference'=>$this->reference(),
                'package_name'=>$package->name,'entitlement_type'=>$package->entitlement_type,
                'entitlement_quantity'=>$package->entitlement_quantity,'duration_days'=>$package->duration_days,
                'gross_amount'=>$gross,'commission_rate'=>$rate,'platform_revenue'=>$platform,'fulfilment_payable'=>$fulfilment,
                'currency'=>'TOK','status'=>'funded','payment_reference'=>'TOOLBOX-'.$this->reference(),'funded_at'=>now()]);
            $correlation=(string)Str::uuid();
            $transaction=$this->tokens->createTransaction(['user_token_wallet_id'=>$wallet->id,'user_id'=>$user->id,'candidate_id'=>$request->candidate_id,
                'tokenable_type'=>$payment::class,'tokenable_id'=>$payment->id,'type'=>'package_redemption','status'=>'completed','action_key'=>'campaign-tool-package',
                'action_label'=>'Fund '.$package->name,'amount'=>-$gross,'balance_before'=>$before,'balance_after'=>$wallet->balance,
                'metadata'=>['correlation_id'=>$correlation,'platform_tokens'=>$platform,'fulfilment_tokens'=>$fulfilment,'token_value_kes'=>config('campaign_tools.token_value_kes',1)],'finalized_at'=>now()]);
            $this->commerce->createLedgerEntry(['campaign_tool_payment_id'=>$payment->id,'entry_type'=>'redemption','gross_amount'=>$gross,
                'platform_amount'=>$platform,'fulfilment_amount'=>$fulfilment,'currency'=>'TOK','correlation_id'=>$correlation,
                'metadata'=>['user_token_transaction_id'=>$transaction->id,'token_value_kes'=>config('campaign_tools.token_value_kes',1)],'occurred_at'=>now()]);
            $lockedRequest->update(['payment_status'=>'paid','status'=>'in_progress']);
        });
    }

    public function transition(CampaignToolRequest $request, User $admin, string $action, ?string $notes): void
    {
        DB::transaction(function () use ($request,$admin,$action,$notes) {
            $request->loadMissing(['payment','package','campaignTool']);
            if ($action==='start_fulfilment') {
                $this->assertFunded($request); $request->update(['status'=>'in_progress','admin_notes'=>$notes]); return;
            }
            if ($action==='activate') {
                $this->assertFunded($request); $payment=$this->commerce->lockedPaymentForRequest($request);
                if ($payment->status==='fulfilled') return;
                $expires=$payment->entitlement_type==='time' ? now()->addDays((int)$payment->duration_days) : null;
                $allowance=$payment->entitlement_type==='quantity' ? (int)$payment->entitlement_quantity : null;
                $this->commerce->createEntitlement(['candidate_id'=>$request->candidate_id,'campaign_tool_id'=>$request->campaign_tool_id,
                    'campaign_tool_package_id'=>$request->campaign_tool_package_id,'campaign_tool_payment_id'=>$payment->id,'tool_key'=>$request->tool_key,
                    'entitlement_type'=>$payment->entitlement_type,'allowance'=>$allowance,'remaining_allowance'=>$allowance,'status'=>'active',
                    'activated_at'=>now(),'expires_at'=>$expires,'activated_by'=>$admin->id]);
                $payment->update(['status'=>'fulfilled','fulfilled_at'=>now()]); $request->update(['status'=>'completed','admin_notes'=>$notes]); return;
            }
            if ($action==='refund') {
                $this->assertFunded($request); $payment=$this->commerce->lockedPaymentForRequest($request);
                if ($payment->status!=='funded') throw ValidationException::withMessages(['action'=>'Only an unfulfilled funded package can be refunded.']);
                $correlation=(string)Str::uuid();
                $wallet=$this->tokens->lockedWallet($payment->user_id);
                $before=$wallet->balance;
                $refund=(int)$payment->gross_amount;
                $wallet->update(['balance'=>$before+$refund]);
                $transaction=$this->tokens->createTransaction(['user_token_wallet_id'=>$wallet->id,'user_id'=>$payment->user_id,
                    'candidate_id'=>$payment->candidate_id,'tokenable_type'=>$payment::class,'tokenable_id'=>$payment->id,
                    'type'=>'package_refund','status'=>'completed','action_key'=>'campaign-tool-package-refund',
                    'action_label'=>'Refund '.$payment->package_name,'amount'=>$refund,'balance_before'=>$before,'balance_after'=>$wallet->balance,
                    'metadata'=>['correlation_id'=>$correlation,'campaign_tool_request_id'=>$request->id],'finalized_at'=>now()]);
                $this->commerce->createLedgerEntry(['campaign_tool_payment_id'=>$payment->id,'entry_type'=>'refund','gross_amount'=>-$payment->gross_amount,
                    'platform_amount'=>-$payment->platform_revenue,'fulfilment_amount'=>-$payment->fulfilment_payable,'currency'=>$payment->currency,
                    'correlation_id'=>$correlation,'metadata'=>['admin_id'=>$admin->id,'note'=>$notes,'user_token_transaction_id'=>$transaction->id],'occurred_at'=>now()]);
                $payment->update(['status'=>'refunded','refunded_amount'=>$payment->gross_amount,'refunded_at'=>now()]);
                $request->update(['status'=>'cancelled','payment_status'=>'refunded','admin_notes'=>$notes]); return;
            }
            if ($action==='reject') { $request->update(['status'=>'cancelled','admin_notes'=>$notes]); return; }
            throw ValidationException::withMessages(['action'=>'Invalid campaign-tool transition.']);
        });
    }

    public function consumeEntitlement(int $candidateId, string $toolKey): void
    {
        DB::transaction(function () use ($candidateId,$toolKey): void {
            $entitlement=$this->commerce->lockedActiveEntitlement($candidateId,$toolKey);
            if (! $entitlement) throw ValidationException::withMessages(['tool'=>'This campaign-tool package is not active or has been exhausted.']);
            if ($entitlement->entitlement_type==='quantity') {
                $remaining=max(0,(int)$entitlement->remaining_allowance-1);
                $entitlement->update(['remaining_allowance'=>$remaining,'status'=>$remaining===0?'exhausted':'active','fulfilled_at'=>$remaining===0?now():null]);
            } elseif ($entitlement->entitlement_type==='one_time') {
                $entitlement->update(['status'=>'fulfilled','fulfilled_at'=>now()]);
            }
        });
    }

    private function assertFunded(CampaignToolRequest $request): void
    {
        if (! $request->payment || $request->payment->status!=='funded') throw ValidationException::withMessages(['action'=>'Verified payment is required before fulfilment or activation.']);
    }
    private function reference(): string { return substr('CTP'.now()->format('ymdHis').Str::upper(Str::random(10)),0,40); }
}
