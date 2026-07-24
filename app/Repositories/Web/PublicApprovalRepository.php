<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicApprovalRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicApprovalRepository implements PublicApprovalRepositoryInterface
{
    private const ENDPOINT = 'https://api.politiqkenya.com/public/monitored-profile-example';

    public function approvalForProfile(string $profileSlug): ?float
    {
        try {
            $response = Http::connectTimeout((int) config('services.politiqkenya.connect_timeout', 15))
                ->timeout((int) config('services.politiqkenya.timeout', 120))
                ->acceptJson()
                ->get(config('services.politiqkenya.public_approval_endpoint', self::ENDPOINT), [
                    'profile' => $profileSlug,
                    'window' => '30d',
                    'country_code' => 'KE',
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Public approval API connection failed.', [
                'profile' => $profileSlug,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $this->extractApprovalScore($response->json());
    }

    private function extractApprovalScore(mixed $payload): ?float
    {
        if (is_numeric($payload)) {
            return (float) $payload;
        }

        if (is_array($payload)) {
            $score = data_get($payload, 'public_pressure.score');

            if (is_numeric($score)) {
                return (float) $score;
            }

            $score = data_get($payload, 'approval');

            if (is_numeric($score)) {
                return (float) $score;
            }
        }

        return null;
    }
}

