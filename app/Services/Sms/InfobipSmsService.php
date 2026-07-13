<?php

namespace App\Services\Sms;

use App\Models\CandidateSmsSetting;
use App\Support\PhoneNumber;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InfobipSmsService
{
    /**
     * @throws RequestException
     */
    public function sendBulk(CandidateSmsSetting $setting, Collection $recipients, string $message): array
    {
        $phones = $this->recipientPhones($recipients);

        if ($phones->isEmpty()) {
            return [
                'success' => false,
                'recipient_count' => 0,
                'responses' => [],
                'message' => 'No valid phone numbers were found for the scoped voters.',
            ];
        }

        $responses = [];

        foreach ($phones->chunk(config('sms.infobip.chunk_size', 500)) as $chunk) {
            $responses[] = Http::withHeaders([
                'Authorization' => $this->authorizationHeader($setting->api_key),
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
            'message' => 'Bulk SMS sent.',
        ];
    }

    private function recipientPhones(Collection $recipients): Collection
    {
        return $recipients
            ->map(fn ($recipient): ?string => PhoneNumber::normalizeKenyan($recipient->phone ?? null))
            ->filter()
            ->unique()
            ->values();
    }

    private function authorizationHeader(string $apiKey): string
    {
        return Str::startsWith($apiKey, ['App ', 'Basic ', 'Bearer ']) ? $apiKey : 'App ' . $apiKey;
    }

    private function url(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim(config('sms.infobip.endpoint'), '/');
    }
}
