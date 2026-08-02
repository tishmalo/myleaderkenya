<?php

namespace App\Contracts\Services;

interface PublicPulseEngineClientInterface
{
    public function submitJob(array $payload): array;

    public function jobStatus(string $engineJobId): array;

    public function mentions(string $engineJobId, array $filters = []): array;

    public function tweets(string $engineJobId, array $filters = []): array;

    public function checkAccount(array $payload): array;
}
