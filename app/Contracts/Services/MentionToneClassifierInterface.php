<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface MentionToneClassifierInterface
{
    public function classify(Collection $mentions, array $context = []): array;
}
