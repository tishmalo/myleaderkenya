<?php

namespace App\Contracts\Services;

interface MentionLanguageDetectorInterface
{
    public function detect(string $text): string;

    public function isObviousNeutral(string $text): bool;
}
