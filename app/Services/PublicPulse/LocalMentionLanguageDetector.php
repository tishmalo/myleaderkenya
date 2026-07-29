<?php

namespace App\Services\PublicPulse;

use App\Contracts\Services\MentionLanguageDetectorInterface;
use Illuminate\Support\Str;

class LocalMentionLanguageDetector implements MentionLanguageDetectorInterface
{
    private const SWAHILI_HINTS = [
        'huyu', 'hiyo', 'hawa', 'watu', 'wananchi', 'serikali', 'uchaguzi', 'kura',
        'rais', 'mgombea', 'bunge', 'kaunti', 'siasa', 'uongozi', 'amefanya',
    ];

    private const SHENG_HINTS = [
        'msee', 'mzae', 'niko', 'rada', 'story', 'mboka', 'hustle', 'maze',
        'kusema', 'dem', 'sonko', 'sasa', 'uko', 'ameweza',
    ];

    public function detect(string $text): string
    {
        $clean = Str::of($text)->lower()->replaceMatches('/[^\pL\s]/u', ' ')->value();
        $tokens = collect(preg_split('/\s+/u', $clean) ?: [])->filter()->values();

        if ($tokens->isEmpty()) {
            return 'unknown';
        }

        $swahili = $tokens->intersect(self::SWAHILI_HINTS)->count();
        $sheng = $tokens->intersect(self::SHENG_HINTS)->count();
        $englishHints = $tokens->intersect(['the', 'and', 'for', 'with', 'this', 'that', 'vote', 'leader', 'president'])->count();

        if (($swahili + $sheng) > 0 && $englishHints > 0) {
            return 'mixed';
        }

        if ($sheng > 0) {
            return 'sheng';
        }

        if ($swahili > 0) {
            return 'sw';
        }

        if ($englishHints > 0 || preg_match('/^[\x00-\x7F]+$/', $text)) {
            return 'en';
        }

        return 'unknown';
    }

    public function isObviousNeutral(string $text): bool
    {
        $clean = trim(Str::of($text)->lower()->replaceMatches('/https?:\/\/\S+/u', '')->value());

        if ($clean === '' || mb_strlen($clean) < 18) {
            return true;
        }

        return (bool) preg_match('/^(photo|video|live|watch|read more|breaking)$/iu', $clean);
    }
}
