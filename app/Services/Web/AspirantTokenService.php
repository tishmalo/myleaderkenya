<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenRateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenTransactionRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateTokenWalletRepositoryInterface;
use App\Models\Candidate;
use App\Models\CandidateSmsMessage;
use App\Models\CandidateTokenPackage;
use App\Models\CandidateTokenTransaction;
use App\Models\CandidateTokenWallet;
use App\Models\User;
use App\Services\Sms\SmsCostCalculator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AspirantTokenService
{
    public const INITIAL_GRANT = 20;

    public function __construct(
        private CandidateTokenWalletRepositoryInterface $walletRepository,
        private CandidateTokenPackageRepositoryInterface $packageRepository,
        private CandidateTokenRateRepositoryInterface $rateRepository,
        private CandidateTokenPurchaseRepositoryInterface $purchaseRepository,
        private CandidateTokenTransactionRepositoryInterface $transactionRepository,
        private SmsCostCalculator $smsCostCalculator
    ) {}

    public function walletForCandidate(Candidate $candidate): CandidateTokenWallet
    {
        $wallet = $this->walletRepository->firstOrCreateForCandidate($candidate);

        if (! $wallet->initial_granted_at) {
            $this->grantInitialTokens($candidate);
            $wallet->refresh();
        }

        return $wallet;
    }

    public function activePackages()
    {
        return $this->packageRepository->active();
    }

    public function activeRates()
    {
        return $this->rateRepository->active();
    }

    public function quoteFixed(string $actionKey, int $quantity = 1): array
    {
        $rate = $this->rateFor($actionKey);
        $quantity = max(1, $quantity);

        return [
            'action_key' => $rate->action_key,
            'action_label' => $rate->label,
            'calculation_type' => $rate->calculation_type,
            'quantity' => $quantity,
            'unit_tokens' => $rate->token_amount,
            'tokens_required' => $rate->token_amount * $quantity,
            'description' => $rate->description,
        ];
    }

    public function quoteBulkSms(string $message, int $recipientCount): array
    {
        $rate = $this->rateFor('bulk-sms');
        $calculation = $this->smsCostCalculator->calculate($message, $recipientCount, $rate->token_amount);

        return array_merge($calculation, [
            'action_key' => $rate->action_key,
            'action_label' => $rate->label,
            'calculation_type' => $rate->calculation_type,
            'unit_tokens' => $rate->token_amount,
            'quantity' => $calculation['sms_units'],
            'description' => $rate->description,
        ]);
    }

    public function purchaseTokens(Candidate $candidate, User $user, CandidateTokenPackage $package, array $data)
    {
        return DB::transaction(function () use ($candidate, $user, $package, $data) {
            $wallet = $this->lockedWallet($candidate);
            $purchase = $this->purchaseRepository->create([
                'candidate_id' => $candidate->id,
                'user_id' => $user->id,
                'candidate_token_package_id' => $package->id,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'package_name' => $package->name,
                'token_amount' => $package->token_amount,
                'price' => $package->price,
                'currency' => $package->currency,
                'payment_reference' => $data['payment_reference'] ?? null,
                'status' => 'credited',
                'credited_at' => now(),
            ]);

            $this->credit($wallet, $candidate, $user, $package->token_amount, [
                'type' => 'purchase',
                'candidate_token_purchase_id' => $purchase->id,
                'action_label' => 'Token purchase: ' . $package->name,
                'metadata' => [
                    'package_name' => $package->name,
                    'price' => $package->price,
                    'currency' => $package->currency,
                    'payment_reference' => $data['payment_reference'] ?? null,
                ],
            ]);

            return $purchase;
        });
    }

    public function reserveBulkSms(Candidate $candidate, User $user, array $quote): CandidateTokenTransaction
    {
        return DB::transaction(function () use ($candidate, $user, $quote): CandidateTokenTransaction {
            $wallet = $this->lockedWallet($candidate);
            $amount = (int) $quote['tokens_required'];

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient tokens. Required: ' . number_format($amount) . ', available: ' . number_format($wallet->balance) . '.');
            }

            $before = $wallet->balance;
            $wallet->update(['balance' => $before - $amount]);

            return $this->transactionRepository->create([
                'candidate_id' => $candidate->id,
                'candidate_token_wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'reservation',
                'status' => 'reserved',
                'action_key' => 'bulk-sms',
                'action_label' => 'Bulk SMS',
                'calculation_type' => 'per_sms_unit',
                'quantity' => (int) $quote['sms_units'],
                'unit_tokens' => (int) $quote['unit_tokens'],
                'amount' => -$amount,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'metadata' => $quote,
            ]);
        });
    }

    public function debitAction(Candidate $candidate, User $user, array $quote, ?object $tokenable = null): CandidateTokenTransaction
    {
        return DB::transaction(function () use ($candidate, $user, $quote, $tokenable): CandidateTokenTransaction {
            $wallet = $this->lockedWallet($candidate);
            $amount = (int) $quote['tokens_required'];

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient tokens. Required: ' . number_format($amount) . ', available: ' . number_format($wallet->balance) . '.');
            }

            $before = $wallet->balance;
            $wallet->update(['balance' => $before - $amount]);

            return $this->transactionRepository->create([
                'candidate_id' => $candidate->id,
                'candidate_token_wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'tokenable_type' => $tokenable ? $tokenable::class : null,
                'tokenable_id' => $tokenable?->id,
                'type' => 'debit',
                'status' => 'completed',
                'action_key' => $quote['action_key'],
                'action_label' => $quote['action_label'],
                'calculation_type' => $quote['calculation_type'],
                'quantity' => (int) $quote['quantity'],
                'unit_tokens' => (int) $quote['unit_tokens'],
                'amount' => -$amount,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'metadata' => $quote,
                'finalized_at' => now(),
            ]);
        });
    }

    public function attachReservationToSms(CandidateTokenTransaction $reservation, CandidateSmsMessage $message): void
    {
        $this->transactionRepository->update($reservation, [
            'tokenable_type' => $message::class,
            'tokenable_id' => $message->id,
        ]);
    }

    public function finalizeReservation(?CandidateTokenTransaction $reservation): void
    {
        if (! $reservation || $reservation->status !== 'reserved') {
            return;
        }

        $this->transactionRepository->update($reservation, [
            'type' => 'debit',
            'status' => 'completed',
            'finalized_at' => now(),
        ]);
    }

    public function refundReservation(?CandidateTokenTransaction $reservation, string $reason): void
    {
        if (! $reservation || $reservation->status !== 'reserved') {
            return;
        }

        DB::transaction(function () use ($reservation, $reason): void {
            $wallet = CandidateTokenWallet::whereKey($reservation->candidate_token_wallet_id)->lockForUpdate()->first();

            if (! $wallet) {
                return;
            }

            $refundAmount = abs((int) $reservation->amount);
            $before = $wallet->balance;
            $wallet->update(['balance' => $before + $refundAmount]);

            $this->transactionRepository->update($reservation, [
                'status' => 'refunded',
                'finalized_at' => now(),
            ]);

            $this->transactionRepository->create([
                'candidate_id' => $reservation->candidate_id,
                'candidate_token_wallet_id' => $wallet->id,
                'user_id' => $reservation->user_id,
                'tokenable_type' => $reservation->tokenable_type,
                'tokenable_id' => $reservation->tokenable_id,
                'type' => 'refund',
                'status' => 'completed',
                'action_key' => $reservation->action_key,
                'action_label' => $reservation->action_label,
                'calculation_type' => $reservation->calculation_type,
                'quantity' => $reservation->quantity,
                'unit_tokens' => $reservation->unit_tokens,
                'amount' => $refundAmount,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'metadata' => array_merge($reservation->metadata ?? [], ['refund_reason' => $reason]),
                'finalized_at' => now(),
            ]);
        });
    }

    private function grantInitialTokens(Candidate $candidate): void
    {
        DB::transaction(function () use ($candidate): void {
            $wallet = $this->lockedWallet($candidate);

            if ($wallet->initial_granted_at) {
                return;
            }

            $this->credit($wallet, $candidate, null, self::INITIAL_GRANT, [
                'type' => 'grant',
                'action_label' => 'Initial token grant',
                'metadata' => ['reason' => 'Initial aspirant token balance'],
            ]);

            $wallet->update(['initial_granted_at' => now()]);
        });
    }

    private function credit(CandidateTokenWallet $wallet, Candidate $candidate, ?User $user, int $amount, array $context): CandidateTokenTransaction
    {
        $before = $wallet->balance;
        $wallet->update(['balance' => $before + $amount]);

        return $this->transactionRepository->create([
            'candidate_id' => $candidate->id,
            'candidate_token_wallet_id' => $wallet->id,
            'user_id' => $user?->id,
            'candidate_token_purchase_id' => $context['candidate_token_purchase_id'] ?? null,
            'type' => $context['type'],
            'status' => 'completed',
            'action_label' => $context['action_label'],
            'calculation_type' => 'fixed',
            'quantity' => 1,
            'unit_tokens' => $amount,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $wallet->balance,
            'metadata' => $context['metadata'] ?? [],
            'finalized_at' => now(),
        ]);
    }

    private function lockedWallet(Candidate $candidate): CandidateTokenWallet
    {
        $this->walletRepository->firstOrCreateForCandidate($candidate);

        return CandidateTokenWallet::where('candidate_id', $candidate->id)->lockForUpdate()->firstOrFail();
    }

    private function rateFor(string $actionKey)
    {
        $rate = $this->rateRepository->findActiveByActionKey($actionKey);

        if (! $rate) {
            throw new RuntimeException("No active token rate is configured for {$actionKey}.");
        }

        return $rate;
    }
}
