<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\AspirantSupportRepositoryInterface;
use App\Models\AspirantSupportPayment;
use App\Models\Candidate;
use App\Models\CandidateTokenPackage;
use App\Models\User;
use App\Services\Api\IpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AspirantSupportService
{
    public function __construct(
        private AspirantSupportRepositoryInterface $supports,
        private IpayService $ipay
    ) {}

    public function start(User $user, CandidateTokenPackage $package, array $data): string
    {
        $this->ipay->assertConfigured();
        $candidate = Candidate::query()->whereKey($data['candidate_id'])->where('approval_status', 'approved')->first();
        if (! $candidate) {
            throw ValidationException::withMessages(['candidate_id' => 'Select an approved aspirant.']);
        }

        $gross = round((float) $package->price, 2);
        $rate = max(0, min(100, (float) config('campaign_tools.platform_commission_percent', 20)));
        $fee = round($gross * ($rate / 100), 2);
        $support = $this->supports->create([
            'user_id' => $user->id,
            'candidate_id' => $candidate->id,
            'candidate_token_package_id' => $package->id,
            'supporter_name' => $data['name'],
            'supporter_email' => $data['email'],
            'supporter_phone' => $data['phone'],
            'message' => $data['message'],
            'checkout_reference' => $this->uniqueReference(),
            'gross_amount' => $gross,
            'platform_fee_rate' => $rate,
            'platform_fee_amount' => $fee,
            'aspirant_amount' => round($gross - $fee, 2),
            'currency' => $package->currency,
            'status' => 'pending',
        ]);

        return $this->ipay->aspirantSupportCheckoutUrl($support, $user, $data);
    }

    public function complete(array $callback): array
    {
        $reference = $this->ipay->callbackReference($callback);
        if (! $reference) return ['status' => 'failed', 'message' => 'The payment callback had no order reference.'];
        $verification = $this->ipay->verifyTransaction($reference);

        return DB::transaction(function () use ($reference, $verification, $callback): array {
            $support = $this->supports->lockedByReference($reference);
            if (! $support) return ['status' => 'failed', 'message' => 'Aspirant support payment was not found.'];
            $gateway = [
                'gateway_transaction_code' => $verification['transaction_code'],
                'gateway_status' => $verification['status'],
                'gateway_response' => ['callback' => $callback, 'verification' => $verification['raw']],
                'callback_received_at' => now(),
            ];
            if ($support->status === 'paid') {
                $this->supports->update($support, $gateway);
                return ['status' => 'success', 'message' => 'This support payment was already recorded.'];
            }
            if ($verification['status'] !== 'success') {
                $status = $verification['status'] === 'pending' ? 'pending' : 'failed';
                $this->supports->update($support, $gateway + ['status' => $status]);
                return ['status' => $status, 'message' => 'The support payment was not confirmed.'];
            }
            if (abs((float) $verification['amount'] - (float) $support->gross_amount) >= 0.01) {
                $this->supports->update($support, $gateway + ['status' => 'failed']);
                return ['status' => 'failed', 'message' => 'The confirmed amount does not match the support amount.'];
            }
            if (! empty($verification['currency']) && strtoupper((string) $verification['currency']) !== strtoupper($support->currency)) {
                $this->supports->update($support, $gateway + ['status' => 'failed']);
                return ['status' => 'failed', 'message' => 'The confirmed payment currency does not match.'];
            }
            $this->supports->update($support, $gateway + [
                'payment_reference' => $verification['transaction_code'] ?: $reference,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            return ['status' => 'success', 'message' => 'Your support was sent directly to the aspirant.'];
        });
    }

    public function dataForSupporter(User $user): array
    {
        return ['directSupports' => $this->supports->forSupporter($user)];
    }

    public function dataForCandidate(int $candidateId): array
    {
        return [
            'supports' => $this->supports->forCandidate($candidateId),
            'supportTotal' => $this->supports->paidTotalForCandidate($candidateId),
        ];
    }

    public function reply(User $user, int $candidateId, int $supportId, string $reply): void
    {
        $support = $this->supports->findForCandidate($supportId, $candidateId);
        if ($support->replied_at) {
            throw ValidationException::withMessages(['reply' => 'A reply has already been sent for this support message.']);
        }
        $this->supports->update($support, ['aspirant_reply' => $reply, 'replied_at' => now(), 'replied_by' => $user->id]);
    }

    private function uniqueReference(): string
    {
        do { $reference = 'SUP'.now()->format('ymdHis').Str::upper(Str::random(8)); }
        while ($this->supports->findByReference($reference));
        return substr($reference, 0, 30);
    }
}
