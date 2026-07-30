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
    public function member(string $slug): array { return $this->request('members/'.rawurlencode($slug)); }

    private function request(string $path): array
    {
        try {
            $response = $this->client()->get($path)->throw();
            $payload = $response->json();
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Parliament members API connection failed.');
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Parliament members API returned HTTP '.$exception->response->status().'.'
            );
        }

        if (! is_array($payload) || (($payload['success'] ?? true) !== true)) {
            throw new RuntimeException('Parliament members API returned an invalid response.');
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