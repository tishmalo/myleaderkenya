<?php

namespace App\Support;

/**
 * Aspirants store their location as free-text names ("Nairobi", "Nairobi
 * County", "Nyandarua North") while links are attached to the normalised
 * counties, constituencies and wards tables. Comparing the two literally is
 * brittle, so matching goes through this helper: it expands a free-text name
 * into the handful of spellings a region row may legitimately use.
 */
final class LocationName
{
    /**
     * Every spelling a region row may use for the given free-text name.
     *
     * @return array<int, string>
     */
    public static function variants(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return [];
        }

        $base = preg_replace('/\s+county$/i', '', $name) ?? $name;
        $variants = [$name, $base];

        // Only add the suffixed form when the raw value did not already have it.
        if (strcasecmp($base, $name) === 0) {
            $variants[] = $base.' County';
        }

        return array_values(array_unique(array_filter($variants, fn (string $variant) => $variant !== '')));
    }
}
