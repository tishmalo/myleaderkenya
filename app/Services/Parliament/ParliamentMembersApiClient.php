<?php

namespace App\Services\Parliament;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ParliamentMembersApiClient
{
    public function members(): array { return $this->request('members'); }
    public function member(string $slug, ?string $house = null): array
    {
        $normalizedHouse = match (strtolower(trim((string) $house))) {
            'senate' => 'senate',
            'national assembly', 'national-assembly', 'national_assembly' => 'national-assembly',
            default => null,
        };

        return $this->request(
            'members/'.rawurlencode($slug),
            $normalizedHouse ? ['house' => $normalizedHouse] : []
        );
    }

    private function request(string $path, array $query = []): array
    {
        $endpoint = rtrim((string) config('services.parliament_members.base_url'), '/')
            .'/'.ltrim($path, '/');
        if ($query !== []) {
            $endpoint .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        try {
            $response = $this->client()->get($path, $query)->throw();
            $payload = $response->json();
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Parliament members API connection failed for '.$endpoint.'.'
            );
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Parliament members API returned HTTP '.$exception->response->status()
                .' for '.$endpoint.'.'
            );
        }

        if (! is_array($payload) || (($payload['success'] ?? true) !== true)) {
            throw new RuntimeException(
                'Parliament members API returned an invalid response for '.$endpoint.'.'
            );
        }

        return $payload;
    }

    private function client(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.parliament_members.base_url'), '/');
        $token = (string) config('services.parliament_members.token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Parliament members API is not configured.');
        }

        return Http::baseUrl($baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->connectTimeout((int) config('services.parliament_members.connect_timeout', 10))
            ->timeout((int) config('services.parliament_members.timeout', 45))
            ->retry(2, 1500, throw: false);
    }
}