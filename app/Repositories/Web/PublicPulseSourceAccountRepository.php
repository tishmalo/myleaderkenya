<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Models\PublicPulseSourceAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PublicPulseSourceAccountRepository implements PublicPulseSourceAccountRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return PublicPulseSourceAccount::query()
            ->when($filters['provider'] ?? null, fn ($query, $provider) => $query->where('provider', $provider))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('label', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("status = 'healthy' desc")
            ->latest('last_health_check_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): PublicPulseSourceAccount
    {
        return PublicPulseSourceAccount::create($data);
    }

    public function update(PublicPulseSourceAccount $account, array $data): PublicPulseSourceAccount
    {
        $account->update($data);

        return $account->refresh();
    }

    public function activeForProvider(string $provider, int $limit = 10): Collection
    {
        return PublicPulseSourceAccount::query()
            ->where('provider', $provider)
            ->where('status', PublicPulseSourceAccount::STATUS_HEALTHY)
            ->whereNull('replaced_at')
            ->where(function ($query): void {
                $query->whereNull('cooldown_until')
                    ->orWhere('cooldown_until', '<=', now());
            })
            ->orderByDesc('last_success_at')
            ->limit($limit)
            ->get();
    }

    public function accountsDueForHealthCheck(int $limit = 50): Collection
    {
        $intervalMinutes = (int) config('services.public_pulse_x.health_check_interval_minutes', 30);

        return PublicPulseSourceAccount::query()
            ->whereNull('replaced_at')
            ->where(function ($query) use ($intervalMinutes): void {
                $query->whereNull('last_health_check_at')
                    ->orWhere('last_health_check_at', '<=', now()->subMinutes($intervalMinutes));
            })
            ->orderBy('last_health_check_at')
            ->limit($limit)
            ->get();
    }

    public function recordHealth(PublicPulseSourceAccount $account, array $result): PublicPulseSourceAccount
    {
        $status = $result['status'] ?? PublicPulseSourceAccount::STATUS_UNKNOWN_ERROR;
        $isHealthy = $status === PublicPulseSourceAccount::STATUS_HEALTHY;
        $failureCount = $isHealthy ? $account->failure_count : $account->failure_count + 1;
        $consecutiveFailureCount = $isHealthy ? 0 : $account->consecutive_failure_count + 1;

        $account->forceFill([
            'status' => $status,
            'last_health_check_at' => now(),
            'last_success_at' => $isHealthy ? now() : $account->last_success_at,
            'failure_count' => $failureCount,
            'consecutive_failure_count' => $consecutiveFailureCount,
            'last_error_code' => $result['error_code'] ?? null,
            'last_error_message' => $result['message'] ?? null,
            'last_result_count' => $result['result_count'] ?? null,
            'median_result_ratio' => $result['median_result_ratio'] ?? null,
            'cooldown_until' => $result['cooldown_until'] ?? null,
            'issue_notified_at' => $isHealthy ? null : $account->issue_notified_at,
        ])->save();

        return $account->refresh();
    }

    public function recordEngineFailure(
        PublicPulseSourceAccount $account,
        string $status,
        string $reason,
        mixed $cooldownUntil = null
    ): PublicPulseSourceAccount {
        $account->forceFill([
            'status' => $status,
            'last_health_check_at' => now(),
            'failure_count' => $account->failure_count + 1,
            'consecutive_failure_count' => $account->consecutive_failure_count + 1,
            'last_error_code' => 'pulse_engine_report',
            'last_error_message' => str($reason)->limit(2000),
            'cooldown_until' => $cooldownUntil,
        ])->save();

        return $account->refresh();
    }

    public function markIssueNotified(PublicPulseSourceAccount $account): void
    {
        $account->forceFill(['issue_notified_at' => now()])->save();
    }
}
