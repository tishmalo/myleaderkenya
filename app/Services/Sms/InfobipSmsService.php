<?php

namespace App\Services\Sms;

use App\Models\CandidateSmsSetting;
use App\Support\PhoneNumber;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InfobipSmsService
{
    /**
     * @throws RequestException
     */
    public function sendBulk(CandidateSmsSetting $setting, Collection $recipients, string $message): array
    {
        $phones = $this->recipientPhones($recipients);

        if ($phones->isEmpty()) {
            Log::warning('Bulk SMS send skipped because no valid recipient phone numbers were found.');

            return [
                'success' => false,
                'recipient_count' => 0,
                'responses' => [],
                'message' => 'No valid phone numbers were found for the scoped voters.',
            ];
        }

        $responses = [];

        foreach ($phones->chunk(config('sms.infobip.chunk_size', 500)) as $chunk) {
            Log::info('Sending Bulk SMS chunk to Infobip.', [
                'recipient_count' => $chunk->count(),
                'base_url' => $this->maskedBaseUrl($setting->base_url),
                'sender_name' => $setting->sender_name,
            ]);

            $responses[] = Http::withBasicAuth($setting->username, $setting->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->timeout(config('sms.infobip.timeout', 30))
                ->post($this->url($setting->base_url), [
                    'from' => $setting->sender_name,
                    'to' => $chunk->values()->all(),
                    'text' => $message,
                ])
                ->throw()
                ->json();
        }

        return [
            'success' => true,
            'recipient_count' => $phones->count(),
            'responses' => $responses,
            'message' => 'Infobip accepted the bulk SMS request.',
        ];
    }


    public function accountBalance(CandidateSmsSetting $setting): array
    {
        if (! $setting->isReady()) {
            return [
                'available' => false,
                'amount' => null,
                'currency' => null,
                'formatted' => 'Unavailable',
                'raw' => null,
                'error' => 'SMS provider credentials are not complete.',
            ];
        }

        try {
            $payload = Http::withBasicAuth($setting->username, $setting->password)
                ->acceptJson()
                ->timeout(config('sms.infobip.timeout', 30))
                ->get($this->balanceUrl($setting->base_url))
                ->throw()
                ->json();

            $amount = $this->extractBalanceAmount($payload);
            $currency = $this->extractBalanceCurrency($payload);

            return [
                'available' => $amount !== null,
                'amount' => $amount,
                'currency' => $currency,
                'formatted' => $amount !== null
                    ? trim(number_format((float) $amount, 2) . ' ' . ($currency ?: ''))
                    : 'Unavailable',
                'raw' => $payload,
                'error' => $amount === null ? 'Balance response did not include an amount.' : null,
            ];
        } catch (\Throwable $exception) {
            Log::warning('Unable to retrieve Infobip SMS account balance.', [
                'candidate_id' => $setting->candidate_id,
                'base_url' => $this->maskedBaseUrl($setting->base_url),
                'message' => $exception->getMessage(),
            ]);

            return [
                'available' => false,
                'amount' => null,
                'currency' => null,
                'formatted' => 'Unavailable',
                'raw' => null,
                'error' => 'SMS provider balance could not be retrieved.',
            ];
        }
    }
    private function recipientPhones(Collection $recipients): Collection
    {
        return $recipients
            ->map(fn ($recipient): ?string => PhoneNumber::normalizeKenyan($recipient->phone ?? null))
            ->filter()
            ->unique()
            ->values();
    }

    private function url(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim(config('sms.infobip.endpoint'), '/');
    }


    private function balanceUrl(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim(config('sms.infobip.balance_endpoint'), '/');
    }

    private function extractBalanceAmount(mixed $payload): int|float|null
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['balance', 'amount', 'availableBalance', 'accountBalance'] as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return $payload[$key] + 0;
            }
        }

        return null;
    }

    private function extractBalanceCurrency(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['currency', 'currencyCode'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        return null;
    }
    private function maskedBaseUrl(string $baseUrl): string
    {
        return parse_url($baseUrl, PHP_URL_HOST) ?: 'configured';
    }
}

