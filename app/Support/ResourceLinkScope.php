<?php

namespace App\Support;

use App\Models\Candidate;

/**
 * A resource link is either area scoped (county, optionally constituency and
 * ward) or national.
 *
 * National links carry no county. That is what lets a presidential aspirant -
 * who has no county - be given a link at all, and what decides where the link
 * is published: national links belong on the national aspirants page and on
 * their aspirant's profile, never on a county page.
 */
class ResourceLinkScope
{
    /**
     * A link is national when the form says so, or when it is attached to a
     * presidential aspirant, who has no county to scope it to.
     */
    public static function isNational(array $data, ?Candidate $candidate = null): bool
    {
        if (filter_var($data['is_national'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return $candidate?->isPresidential() ?? false;
    }

    /**
     * Drops the transient is_national flag and clears the geographic fields of
     * a national link so a stale county cannot leak back in from the form.
     */
    public static function apply(array $data, ?Candidate $candidate = null): array
    {
        $isNational = self::isNational($data, $candidate);

        unset($data['is_national']);

        if (! $isNational) {
            return $data;
        }

        $data['county_id'] = null;
        $data['constituency_id'] = null;
        $data['ward_id'] = null;

        return $data;
    }
}
