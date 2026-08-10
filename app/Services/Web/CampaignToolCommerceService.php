<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\CampaignToolCommerceRepositoryInterface;
use App\Models\CampaignToolPayment;
use App\Models\CampaignToolRequest;
use App\Models\User;
use App\Services\Api\IpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignToolCommerceService
{
    public function __construct(private CampaignToolCommerceRepositoryInterface $commerce, private IpayService $ipay) {}

    public function startCheckout(User $user, CampaignToolRequest $request, array $contact): string
    {
        abort_unless($request->user_id === $user->id && $request->fulfilment_type === 'paid_package', 404);
        $request->loadMissing(['package','payment']);
        if (! $request->package || ! $request->package->is_active) throw ValidationException::withMessages(['package'=>'This package is no longer available.']);
        if ($request->payment && in_array($request->payment->status,['funded','fulfilled'],true)) throw ValidationException::withMessages(['payment'=>'This package is already paid.']);
        $payment=$request->payment ?: DB::transaction(function () use ($request,$user) {
            $rate=max(0,min(100,(float)config('campaign_tools.platform_commission_percent',20)));
            $gross=(float)$request->package->price; $platform=round($gross*$rate/100,2);
            return $this->commerce->createPayment(['campaign_tool_request_id'=>$request->id,'campaign_tool_package_id'=>$request->package->id,
                'user_id'=>$user->id,'candidate_id'=>$request->candidate_id,'provider'=>'ipay','checkout_reference'=>$this->reference(),
                'package_name'=>$request->package->name,'entitlement_type'=>$request->package->entitlement_type,
                'entitlement_quantity'=>$request->package->entitlement_quantity,'duration_days'=>$request->package->duration_days,
                'gross_amount'=>$gross,'commission_rate'=>$rate,'platform_revenue'=>$platform,'fulfilment_payable'=>$gross-$platform,
                'currency'=>$request->package->currency,'status'=>'pending']);
        });
        $this->ipay->assertConfigured();
        return $this->ipay->campaignToolCheckoutUrl($payment,$user,$request->package,$contact);
    }

    public function completeCallback(array $callback): array
    {
        $reference=$this->ipay->callbackReference($callback);
        if (! $reference) return ['status'=>'failed','message'=>'Payment callback had no reference.'];
        $verification=$this->ipay->verifyTransaction($reference);
        return DB::transaction(function () use ($reference,$verification,$callback) {
            $payment=$this->commerce->lockedPaymentByReference($reference);
            if (! $payment) return ['status'=>'failed','message'=>'Campaign-tool payment was not found.'];
            $gateway=['gateway_transaction_code'=>$verification['transaction_code'],'gateway_status'=>$verification['status'],
                'gateway_response'=>['callback'=>$callback,'verification'=>$verification['raw']],'callback_received_at'=>now()];
            if (in_array($payment->status,['funded','fulfilled'],true)) { $payment->update($gateway); return ['status'=>'success','message'=>'This package was already funded.']; }
            if ($verification['status'] !== 'success') { $payment->update($gateway+['status'=>$verification['status']==='pending'?'pending':'failed']); return ['status'=>$verification['status'],'message'=>'Payment was not confirmed.']; }
            if (abs((float)$verification['amount']-(float)$payment->gross_amount)>=0.01) { $payment->update($gateway+['status'=>'failed']); return ['status'=>'failed','message'=>'Confirmed amount does not match the package price.']; }
            if (! empty($verification['currency']) && strtoupper((string)$verification['currency']) !== strtoupper($payment->currency)) { $payment->update($gateway+['status'=>'failed']); return ['status'=>'failed','message'=>'Confirmed currency does not match the package currency.']; }
            $correlation=(string)Str::uuid();
            $payment->update($gateway+['status'=>'funded','payment_reference'=>$verification['transaction_code'] ?: $reference,'funded_at'=>now()]);
            $this->commerce->createLedgerEntry(['campaign_tool_payment_id'=>$payment->id,'entry_type'=>'payment','gross_amount'=>$payment->gross_amount,
                'platform_amount'=>$payment->platform_revenue,'fulfilment_amount'=>$payment->fulfilment_payable,'currency'=>$payment->currency,
                'correlation_id'=>$correlation,'metadata'=>['gateway_reference'=>$reference],'occurred_at'=>now()]);
            $payment->request->update(['payment_status'=>'paid','status'=>'in_progress']);
            return ['status'=>'success','message'=>'Package paid. Admin will activate it after setup.'];
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
                $this->commerce->createLedgerEntry(['campaign_tool_payment_id'=>$payment->id,'entry_type'=>'refund','gross_amount'=>-$payment->gross_amount,
                    'platform_amount'=>-$payment->platform_revenue,'fulfilment_amount'=>-$payment->fulfilment_payable,'currency'=>$payment->currency,
                    'correlation_id'=>$correlation,'metadata'=>['admin_id'=>$admin->id,'note'=>$notes,'requires_gateway_refund'=>true],'occurred_at'=>now()]);
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
