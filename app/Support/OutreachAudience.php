<?php

namespace App\Support;

/**
 * The audiences an admin can send to, in the order the compose screen shows
 * them. Each key is resolved to actual recipients by
 * App\Services\Admin\OutreachAudienceResolver.
 */
class OutreachAudience
{
    /**
     * key => [label, description]
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const AUDIENCES = [
        'all_aspirants'          => ['All Aspirants', 'Every approved aspirant, using the email and phone on their profile.'],
        'all_governors'          => ['All Governors', 'Approved aspirants standing for Governor.'],
        'all_senators'           => ['All Senators', 'Approved aspirants standing for Senator.'],
        'all_women_reps'         => ['All Women Reps', 'Approved aspirants standing for Women Representative.'],
        'all_mps'                => ['All MPs', 'Approved aspirants standing for Member of Parliament.'],
        'all_mcas'               => ['All MCAs', 'Approved aspirants standing for Member of County Assembly.'],
        'all_voters'             => ['All Voters', 'Every account recorded as a voter or registered voter.'],
        'registered_voters'      => ['Registered Voters', 'Accounts flagged as registered voters.'],
        'non_registered_voters'  => ['Non Registered Voters', 'Accounts that have not confirmed a voter registration.'],
        'all_parties'            => ['All Parties', 'Published political parties, using their contact email and phone.'],
        'all_event_attendees'    => ['All Event Attendees', 'Everyone who registered for an event, once per email address.'],
        'all_support_groups'     => ['All Support Groups', 'Contacts captured under the support groups.'],
        'all_admins'             => ['All Admins', 'Staff accounts that can sign in to the admin area.'],
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return array_map(fn (array $audience): string => $audience[0], self::AUDIENCES);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function all(): array
    {
        return self::AUDIENCES;
    }

    public static function label(string $key): string
    {
        return self::AUDIENCES[$key][0] ?? $key;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::AUDIENCES);
    }
}
