<?php

namespace App\Services\PublicPulse;

use App\Contracts\Services\PublicPulseEngineClientInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PublicPulseEngineClient implements PublicPulseEngineClientInterface
{
    public function submitJob(array $payload): array
    {
        return $this->request()->post('/pulse/jobs', $payload)->throw()->json();
    }

    public function jobStatus(string $engineJobId): array
    {
        return $this->request()->get('/pulse/jobs/'.urlencode($engineJobId))->throw()->json();
    }

    public function tweets(string $engineJobId, array $filters = []): array
    {
        return $this->request()->get('/pulse/jobs/'.urlencode($engineJobId).'/tweets', $filters)->throw()->json();
    }

    public function checkAccount(array $payload): array
    {
        return $this->request()->post('/pulse/scraper/health', $payload)->throw()->json();
    }

    private function request(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.pulse_engine.base_url'), '/');
        $apiKey = (string) config('services.pulse_engine.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Pulse Engine connection is not configured.');
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-Api-Key' => $apiKey])
            ->connectTimeout((int) config('services.pulse_engine.connect_timeout', 10))
            ->timeout((int) config('services.pulse_engine.timeout', 90))
            ->retry(2, 500, throw: false);
    }
}