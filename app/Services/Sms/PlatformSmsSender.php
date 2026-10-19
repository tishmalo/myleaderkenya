<?php

namespace App\Services\Sms;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends admin outreach SMS through the platform's own Infobip account, not a
 * candidate's. Credentials come from the SMS_* environment values.
 */
class PlatformSmsSender
{
    public function isConfigured(): bool
    {
        $config = config('sms.infobip');

        return filled($config['base_url'] ?? null)
            && filled($config['username'] ?? null)
            && filled($config['password'] ?? null)
            && filled($config['sender'] ?? null);
    }

    /**
     * @return array{success: bool, message: string, response: array<string, mixed>|null}
     */
    public function send(string $phone, string $message): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS provider is not configured. Set SMS_BASE_URL, SMS_USERNAME, SMS_PASSWORD and SMS_SENDER.',
                'response' => null,
            ];
        }

        $recipient = PhoneNumber::normalizeKenyan($phone);

        if ($recipient === null) {
            return [
                'success' => false,
                'message' => 'The phone number on this record is not a valid Kenyan number.',
                'response' => null,
            ];
        }

        try {
            $response = Http::withBasicAuth((string) config('sms.infobip.username'), (string) config('sms.infobip.password'))
                ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                ->timeout((int) config('sms.infobip.timeout', 30))
                ->post($this->url(), [
                    'from' => (string) config('sms.infobip.sender'),
                    'to' => $recipient,
                    'text' => $message,
                ])
                ->throw()
                ->json();

            return [
                'success' => true,
                'message' => 'Accepted by the SMS provider.',
                'response' => is_array($response) ? $response : null,
            ];
        } catch (Throwable $exception) {
            Log::warning('Platform outreach SMS failed.', [
                'phone' => $recipient,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'response' => null,
            ];
        }
    }

    private function url(): string
    {
        return rtrim((string) config('sms.infobip.base_url'), '/')
            .'/'.ltrim((string) config('sms.infobip.endpoint', '/restapi/sms/1/text/single'), '/');
    }
}
