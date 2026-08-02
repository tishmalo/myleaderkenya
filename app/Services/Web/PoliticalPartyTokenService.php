<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\PoliticalPartyTokenRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateTokenPackage;
use App\Models\PoliticalParty;
use App\Models\PoliticalPartyTokenWallet;
use App\Models\User;
use App\Services\Api\IpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PoliticalPartyTokenService
{
    public function __construct(
        private IpayService $ipay,
        private PoliticalPartyTokenRepositoryInterface $tokens,
    ) {}

    public function wallet(PoliticalParty $party): PoliticalPartyTokenWallet
    {
        return $this->tokens->wallet($party);

    }

    public function purchase(
        PoliticalParty $party,
        User $user,
        CandidateTokenPackage $package,
        array $contact,
    ): string {
        $this->ipay->assertConfigured();

        $purchase = $this->tokens->createPurchase([
            'political_party_id' => $party->id,
            'user_id' => $user->id,
            'candidate_token_package_id' => $package->id,
            'checkout_reference' => $this->checkoutReference(),
            'package_name' => $package->name,
            'token_amount' => $package->token_amount,
            'price' => $package->price,
            'currency' => $package->currency,
            'status' => 'pending',
        ]);

        return $this->ipay->partyCheckoutUrl($purchase, $user, $package, $contact);
    }

    public function callback(array $callbackData): array
    {
        $reference = $this->ipay->callbackReference($callbackData);

        if (! $reference) {
            return ['status' => 'failed', 'message' => 'Missing payment reference.'];
        }

        $verification = $this->ipay->verifyTransaction($reference);

        return DB::transaction(function () use (
            $reference,
            $verification,
            $callbackData,
        ): array {
            $purchase = $this->tokens->lockedPurchaseByReference($reference);

            if (! $purchase) {
                return [
                    'status' => 'failed',
                    'message' => 'Party token purchase not found.',
                ];
            }

            $gatewayData = [
                'gateway_transaction_code' => $verification['transaction_code'],
                'gateway_status' => $verification['status'],
                'gateway_response' => [
                    'callback' => $callbackData,
                    'verification' => $verification['raw'],
                ],
                'callback_received_at' => now(),
            ];

            if ($purchase->status === 'credited') {
                $this->tokens->updatePurchase($purchase, $gatewayData);

                return [
                    'status' => 'success',
                    'message' => 'This purchase was already credited.',
                ];
            }

            $amountMatches = abs(
                (float) $verification['amount'] - (float) $purchase->price
            ) < 0.01;

            if ($verification['status'] !== 'success' || ! $amountMatches) {
                $this->tokens->updatePurchase($purchase, $gatewayData + ['status' => 'failed']);

                return [
                    'status' => 'failed',
                    'message' => 'Payment was not confirmed for the expected amount.',
                ];
            }

            $wallet = $this->tokens->lockedPartyWallet($purchase->political_party_id);
            $balanceBefore = $wallet->balance;
            $this->tokens->updatePartyWallet(
                $wallet,
                $balanceBefore + $purchase->token_amount,
            );

            $this->tokens->createPartyTransaction([
                'political_party_id' => $purchase->political_party_id,
                'political_party_token_wallet_id' => $wallet->id,
                'user_id' => $purchase->user_id,
                'political_party_token_purchase_id' => $purchase->id,
                'type' => 'purchase',
                'amount' => $purchase->token_amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'metadata' => ['reference' => $reference],
                'finalized_at' => now(),
            ]);

            $this->tokens->updatePurchase($purchase, $gatewayData + [
                'status' => 'credited',
                'payment_reference' => $verification['transaction_code'] ?: $reference,
                'credited_at' => now(),
            ]);

            return [
                'status' => 'success',
                'message' => number_format($purchase->token_amount)
                    .' tokens credited to the party wallet.',
            ];
        });
    }

    public function transfer(
        PoliticalParty $party,
        Candidate $candidate,
        User $user,
        int $amount,
    ): void {
        if ($amount < 1) {
            throw new RuntimeException('Enter at least one token.');
        }

        if (
            $candidate->political_party_id !== $party->id
            || $candidate->approval_status !== 'approved'
        ) {
            throw new RuntimeException(
                'Tokens can only be distributed to approved aspirants in this party.'
            );
        }

        DB::transaction(function () use ($party, $candidate, $user, $amount): void {
            $partyWallet = $this->tokens->lockedPartyWallet($party->id);

            if ($partyWallet->balance < $amount) {
                throw new RuntimeException('Insufficient party token balance.');
            }

            $candidateWallet = $this->tokens->lockedCandidateWallet($candidate);

            $partyBalanceBefore = $partyWallet->balance;
            $candidateBalanceBefore = $candidateWallet->balance;

            $this->tokens->updatePartyWallet(
                $partyWallet,
                $partyBalanceBefore - $amount,
            );
            $this->tokens->updateCandidateWallet(
                $candidateWallet,
                $candidateBalanceBefore + $amount,
            );

            $partyTransaction = $this->tokens->createPartyTransaction([
                'political_party_id' => $party->id,
                'political_party_token_wallet_id' => $partyWallet->id,
                'user_id' => $user->id,
                'candidate_id' => $candidate->id,
                'type' => 'distribution',
                'amount' => -$amount,
                'balance_before' => $partyBalanceBefore,
                'balance_after' => $partyWallet->balance,
                'metadata' => ['candidate' => $candidate->name],
                'finalized_at' => now(),
            ]);

            $candidateTransaction = $this->tokens->createCandidateTransaction([
                'candidate_id' => $candidate->id,
                'candidate_token_wallet_id' => $candidateWallet->id,
                'user_id' => $user->id,
                'type' => 'party_transfer',
                'status' => 'completed',
                'action_label' => 'Tokens from '.$party->name,
                'calculation_type' => 'fixed',
                'quantity' => 1,
                'unit_tokens' => $amount,
                'amount' => $amount,
                'balance_before' => $candidateBalanceBefore,
                'balance_after' => $candidateWallet->balance,
                'metadata' => ['political_party_id' => $party->id],
                'finalized_at' => now(),
            ]);

            $this->tokens->createTransfer([
                'political_party_id' => $party->id,
                'candidate_id' => $candidate->id,
                'user_id' => $user->id,
                'party_transaction_id' => $partyTransaction->id,
                'candidate_transaction_id' => $candidateTransaction->id,
                'amount' => $amount,
            ]);
        });
    }

    private function checkoutReference(): string
    {
        return 'PTY'.now()->format('ymdHis').Str::upper(Str::random(8));
    }
}
