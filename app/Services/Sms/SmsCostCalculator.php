<?php

namespace App\Services\Sms;

class SmsCostCalculator
{
    private const GSM_BASIC = "@£\$¥ !\"#¤%&'()*+,-./0123456789:;<=>?ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    private const GSM_EXTENDED = "^{}\[~]|€";

    public function calculate(string $message, int $recipientCount, int $tokensPerSmsUnit = 1): array
    {
        $encoding = $this->isGsm7($message) ? 'GSM-7' : 'Unicode';
        $characterCount = $encoding === 'GSM-7'
            ? $this->gsmSeptetLength($message)
            : mb_strlen($message);
        $segmentCount = $this->segments($characterCount, $encoding);
        $smsUnits = max(0, $recipientCount) * $segmentCount;

        return [
            'character_count' => $characterCount,
            'encoding' => $encoding,
            'segment_count' => $segmentCount,
            'recipient_count' => max(0, $recipientCount),
            'sms_units' => $smsUnits,
            'tokens_per_sms_unit' => $tokensPerSmsUnit,
            'tokens_required' => $smsUnits * $tokensPerSmsUnit,
        ];
    }

    private function isGsm7(string $message): bool
    {
        foreach (mb_str_split($message) as $character) {
            if ($character === "\n" || $character === "\r") {
                continue;
            }

            if (! str_contains(self::GSM_BASIC, $character) && ! str_contains(self::GSM_EXTENDED, $character)) {
                return false;
            }
        }

        return true;
    }

    private function gsmSeptetLength(string $message): int
    {
        return collect(mb_str_split($message))->sum(function (string $character): int {
            if ($character === "\n" || $character === "\r") {
                return 1;
            }

            return str_contains(self::GSM_EXTENDED, $character) ? 2 : 1;
        });
    }

    private function segments(int $characterCount, string $encoding): int
    {
        if ($characterCount <= 0) {
            return 0;
        }

        if ($encoding === 'GSM-7') {
            return $characterCount <= 160 ? 1 : (int) ceil($characterCount / 153);
        }

        return $characterCount <= 70 ? 1 : (int) ceil($characterCount / 67);
    }
}
