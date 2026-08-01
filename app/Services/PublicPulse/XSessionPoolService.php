<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Models\PublicPulseSourceAccount;
use Illuminate\Support\Collection;

class XSessionPoolService
{
    public function __construct(
        private PublicPulseSourceAccountRepositoryInterface $accounts
    ) {}

    public function usableAccounts(string $provider = 'x_twscrape', int $limit = 10): Collection
    {
        return $this->accounts
            ->activeForProvider($provider, $limit)
            ->filter(fn (PublicPulseSourceAccount $account): bool => $account->isUsable())
            ->values();
    }

    public function nextAccount(string $provider = 'x_twscrape'): ?PublicPulseSourceAccount
    {
        return $this->usableAccounts($provider, 1)->first();
    }
}
