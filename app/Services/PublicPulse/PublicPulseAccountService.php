<?php

namespace App\Services\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Models\PublicPulseSourceAccount;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PublicPulseAccountService
{
    public function __construct(private PublicPulseSourceAccountRepositoryInterface $accounts) {}

    public function engineAccounts(int $limit = 10): array
    {
        return $this->accounts->activeForProvider('x_twscrape', $limit)
            ->map(fn (PublicPulseSourceAccount $account) => $this->engineAccount($account))
            ->filter()
            ->values()
            ->all();
    }

    public function reportInvalid(PublicPulseSourceAccount $account, string $reason): PublicPulseSourceAccount
    {
        $lower = mb_strtolower($reason);
        $status = str_contains($lower, 'rate')
            ? PublicPulseSourceAccount::STATUS_RATE_LIMITED
            : (str_contains($lower, '401') || str_contains($lower, '403') || str_contains($lower, 'auth')
                ? PublicPulseSourceAccount::STATUS_INVALID_SESSION
                : PublicPulseSourceAccount::STATUS_UNKNOWN_ERROR);

        return $this->accounts->recordEngineFailure($account, $status, $reason, $status === PublicPulseSourceAccount::STATUS_RATE_LIMITED ? now()->addMinutes(15) : null);
    }

    public function healthPayload(PublicPulseSourceAccount $account): array
    {
        $credentials = $this->credentials($account);

        return [
            'provider' => $account->provider,
            'username' => $account->username,
            'auth_token' => $credentials['auth_token'],
            'ct0' => $credentials['ct0'],
            'test_query' => config('services.public_pulse_x.health_test_query', 'Kenya'),
        ];
    }

    private function engineAccount(PublicPulseSourceAccount $account): ?array
    {
        try {
            $credentials = $this->credentials($account);
        } catch (InvalidArgumentException) {
            return null;
        }

        return ['id' => $account->id, 'username' => $account->username ?: $account->label] + $credentials;
    }

    private function credentials(PublicPulseSourceAccount $account): array
    {
        $payload = json_decode((string) $account->encrypted_session_payload, true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid X session JSON.');
        }

        $cookies = $payload['cookies'] ?? $payload;
        $normalized = [];

        foreach ($cookies as $key => $cookie) {
            if (is_array($cookie) && isset($cookie['name'], $cookie['value'])) {
                $normalized[(string) $cookie['name']] = (string) $cookie['value'];
            } elseif (is_string($key) && is_scalar($cookie)) {
                $normalized[$key] = (string) $cookie;
            }
        }

        if (blank($normalized['auth_token'] ?? null) || blank($normalized['ct0'] ?? null)) {
            throw new InvalidArgumentException('X session requires auth_token and ct0 cookies.');
        }

        return ['auth_token' => $normalized['auth_token'], 'ct0' => $normalized['ct0']];
    }
}