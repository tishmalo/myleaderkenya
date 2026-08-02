<?php

namespace App\Services\PublicPulse;

use App\Contracts\Services\PublicPulseEngineClientInterface;
use App\Models\PublicPulseSourceAccount;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class XSessionHealthCheckService
{
    public function __construct(
        private PublicPulseAccountService $accounts,
        private PublicPulseEngineClientInterface $engine
    ) {}

    public function check(PublicPulseSourceAccount $account): array
    {
        try {
            $result = $this->engine->checkAccount($this->accounts->healthPayload($account));
        } catch (InvalidArgumentException $exception) {
            return [
                'status' => PublicPulseSourceAccount::STATUS_INVALID_SESSION,
                'error_code' => 'invalid_session_payload',
                'message' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            Log::warning('Pulse Engine X session health check failed.', [
                'account_id' => $account->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'status' => PublicPulseSourceAccount::STATUS_UNKNOWN_ERROR,
                'error_code' => 'pulse_engine_health_failed',
                'message' => $exception->getMessage(),
            ];
        }

        $status = $result['status'] ?? PublicPulseSourceAccount::STATUS_UNKNOWN_ERROR;

        if (! in_array($status, PublicPulseSourceAccount::STATUSES, true)) {
            $status = PublicPulseSourceAccount::STATUS_UNKNOWN_ERROR;
        }

        return [
            'status' => $status,
            'error_code' => $result['error_code'] ?? null,
            'message' => $result['message'] ?? null,
            'result_count' => $result['result_count'] ?? null,
            'median_result_ratio' => $result['median_result_ratio'] ?? null,
            'cooldown_until' => ! empty($result['cooldown_seconds']) ? now()->addSeconds((int) $result['cooldown_seconds']) : null,
        ];
    }
}