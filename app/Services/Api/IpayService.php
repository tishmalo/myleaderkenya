<?php

namespace App\Services\Api;

use App\Models\CandidateTokenPackage;
use App\Models\CandidateTokenPurchase;
use App\Models\PoliticalPartyTokenPurchase;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class IpayService
{
    public function assertConfigured(): void
    {
        $this->vendorId();
        $this->securityKey();
    }

    public function checkoutUrl(CandidateTokenPurchase $purchase, User $user, CandidateTokenPackage $package, array $contact): string
    {
        $fields = $this->checkoutFields($purchase, $user, $package, $contact);
        $fields['hsh'] = $this->checkoutHash($fields);

        return rtrim($this->checkoutEndpoint(), '?').'?'.http_build_query($fields);
    }

    public function partyCheckoutUrl(PoliticalPartyTokenPurchase $purchase, User $user, CandidateTokenPackage $package, array $contact): string
    {
        $fields = $this->partyCheckoutFields($purchase, $user, $package, $contact);
        $fields['hsh'] = $this->checkoutHash($fields);

        return rtrim($this->checkoutEndpoint(), '?').'?'.http_build_query($fields);
    }

    private function partyCheckoutFields(PoliticalPartyTokenPurchase $purchase, User $user, CandidateTokenPackage $package, array $contact): array
    {
        $reference = (string) $purchase->checkout_reference;

        return ['live' => $this->live(), 'oid' => $reference, 'inv' => $reference, 'ttl' => $this->amount($package->price), 'tel' => $this->phone($contact['phone'] ?? ''), 'eml' => $contact['email'] ?? $user->email, 'vid' => $this->vendorId(), 'curr' => $this->currency(), 'p1' => (string) $purchase->political_party_id, 'p2' => (string) $package->id, 'p3' => 'party_tokens', 'p4' => (string) $package->token_amount, 'cbk' => route('party.payments.ipay.callback'), 'cst' => '1', 'crl' => '0'];
    }

    public function checkoutFields(CandidateTokenPurchase $purchase, User $user, CandidateTokenPackage $package, array $contact): array
    {
        $reference = (string) $purchase->checkout_reference;

        return [
            'live' => $this->live(),
            'oid' => $reference,
            'inv' => $reference,
            'ttl' => $this->amount($package->price),
            'tel' => $this->phone($contact['phone'] ?? ''),
            'eml' => $contact['email'] ?? $user->email,
            'vid' => $this->vendorId(),
            'curr' => $this->currency(),
            'p1' => (string) $purchase->candidate_id,
            'p2' => (string) $package->id,
            'p3' => 'tokens',
            'p4' => (string) $package->token_amount,
            'cbk' => route('payments.ipay.callback'),
            'cst' => '1',
            'crl' => '0',
        ];
    }

    public function checkoutHash(array $fields): string
    {
        $data = ($fields['live'] ?? '')
            .($fields['oid'] ?? '')
            .($fields['inv'] ?? '')
            .($fields['ttl'] ?? '')
            .($fields['tel'] ?? '')
            .($fields['eml'] ?? '')
            .($fields['vid'] ?? '')
            .($fields['curr'] ?? '')
            .($fields['p1'] ?? '')
            .($fields['p2'] ?? '')
            .($fields['p3'] ?? '')
            .($fields['p4'] ?? '')
            .($fields['cbk'] ?? '')
            .($fields['cst'] ?? '')
            .($fields['crl'] ?? '');

        return hash_hmac('sha1', $data, $this->securityKey());
    }

    public function verifyTransaction(string $reference): array
    {
        $response = Http::asForm()
            ->timeout($this->timeout())
            ->post($this->statusEndpoint(), [
                'oid' => $reference,
                'vid' => $this->vendorId(),
                'hash' => $this->statusHash($reference),
            ]);

        $payload = $response->json() ?: [
            'header_status' => $response->status(),
            'status' => 0,
            'message' => $response->body(),
        ];

        return [
            'raw' => $payload,
            'status' => $this->normalizeStatus($payload),
            'transaction_code' => $payload['data']['transaction_code'] ?? $payload['txncd'] ?? null,
            'amount' => $payload['data']['transaction_amount'] ?? $payload['mc'] ?? null,
            'reference' => $payload['data']['oid'] ?? $payload['id'] ?? $reference,
        ];
    }

    public function statusHash(string $reference): string
    {
        return hash_hmac('sha256', $reference.$this->vendorId(), $this->securityKey());
    }

    public function callbackReference(array $callbackData): ?string
    {
        return $callbackData['id']
            ?? $callbackData['oid']
            ?? $callbackData['ivm']
            ?? null;
    }

    public function normalizeStatus(array $payload): string
    {
        $status = (string) ($payload['status'] ?? '');
        $reason = Str::upper((string) ($payload['reasonCode'] ?? ''));
        $message = Str::lower((string) ($payload['message'] ?? ''));

        if ($status === '1' || $status === 'aei7p7yrx4ae34' || $reason === 'SUCCESS' || str_contains($message, 'success')) {
            return 'success';
        }

        if (in_array($reason, ['PAYMENT_NOT_RECEIVED', 'TRANSACTION_TIMED_OUT', 'TRANSACTION_EXPIRED', 'NO_PIN_PASSED', 'INSUFFICIENT_BALANCE', 'INCORRECT_PIN', 'ERROR_OCCURRED'], true)) {
            return 'failed';
        }

        if ($status === '0' || ($payload['header_status'] ?? null) === 404) {
            return 'failed';
        }

        return 'pending';
    }

    public function amount(int|float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function phone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '254700000000';
    }

    private function live(): string
    {
        return config('services.ipay.live') ? '1' : '0';
    }

    private function currency(): string
    {
        return (string) config('services.ipay.currency', 'KES');
    }

    private function vendorId(): string
    {
        $vendorId = strtolower((string) config('services.ipay.vendor_id'));

        if ($vendorId === '') {
            throw new RuntimeException('iPay vendor ID is not configured.');
        }

        return $vendorId;
    }

    private function securityKey(): string
    {
        $key = (string) config('services.ipay.security_key');

        if ($key === '') {
            throw new RuntimeException('iPay security key is not configured.');
        }

        return $key;
    }

    private function checkoutEndpoint(): string
    {
        return (string) config('services.ipay.checkout_url', 'https://payments.ipayafrica.com/v3/ke');
    }

    private function statusEndpoint(): string
    {
        return (string) config('services.ipay.status_url', 'https://apis.ipayafrica.com/payments/v2/transaction/search');
    }

    private function timeout(): int
    {
        return (int) config('services.ipay.timeout', 30);
    }
}
