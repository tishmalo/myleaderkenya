<?php

namespace App\Repositories\Web;

use App\Contracts\Repositories\Web\PublicApprovalRepositoryInterface;
use Illuminate\Support\Facades\Http;

class PublicApprovalRepository implements PublicApprovalRepositoryInterface
{
    private const ENDPOINT = 'https://api.politiqkenya.com/public/monitored-profile-example';

    public function approvalForProfile(string $profileSlug): ?float
    {
        $response = Http::timeout(8)
            ->retry(1, 200)
            ->acceptJson()
            ->get(self::ENDPOINT, [
                'profile' => $profileSlug,
                'window' => '30d',
                'country_code' => 'KE',
            ]);

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
