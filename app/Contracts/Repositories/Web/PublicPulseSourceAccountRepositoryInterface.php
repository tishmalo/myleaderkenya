<?php

namespace App\Contracts\Repositories\Web;

use App\Models\PublicPulseSourceAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PublicPulseSourceAccountRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): PublicPulseSourceAccount;

    public function update(PublicPulseSourceAccount $account, array $data): PublicPulseSourceAccount;

    public function activeForProvider(string $provider, int $limit = 10): Collection;

    public function accountsDueForHealthCheck(int $limit = 50): Collection;

    public function recordHealth(PublicPulseSourceAccount $account, array $result): PublicPulseSourceAccount;

    public function recordEngineFailure(
        PublicPulseSourceAccount $account,
        string $status,
        string $reason,
        mixed $cooldownUntil = null
    ): PublicPulseSourceAccount;

    public function markIssueNotified(PublicPulseSourceAccount $account): void;
}
