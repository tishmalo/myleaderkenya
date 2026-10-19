<?php

namespace App\Support;

/**
 * The placeholders an admin can drop into an outreach message.
 *
 * Kept free of the database so the substitution rules can be unit tested.
 */
class MessageTemplate
{
    /**
     * Placeholder => what it becomes, in the order shown on the compose screen.
     *
     * @var array<string, string>
     */
    public const VARIABLES = [
        'name'                 => 'Recipient name',
        'aspirant_profile_link'=> 'Aspirant profile link',
        'county'               => 'County',
        'constituency'         => 'Constituency',
        'ward'                 => 'Ward',
        'position'             => 'Position',
        'party'                => 'Political party',
        'email'                => 'Email address',
        'phone'                => 'Phone number',
    ];

    /**
     * @return array<string, string>
     */
    public static function variables(): array
    {
        return self::VARIABLES;
    }

    /**
     * @return list<string>
     */
    public static function tokens(): array
    {
        return array_map(fn (string $key): string => '{'.$key.'}', array_keys(self::VARIABLES));
    }

    /**
     * Substitutes {placeholders} with the recipient's values. A placeholder with
     * no value for this recipient collapses to an empty string rather than
     * leaving raw braces in the message.
     *
     * @param  array<string, mixed>  $values
     */
    public static function render(string $template, array $values): string
    {
        $replacements = [];

        foreach (array_keys(self::VARIABLES) as $key) {
            $value = $values[$key] ?? '';
            $replacements['{'.$key.'}'] = is_scalar($value) ? trim((string) $value) : '';
        }

        return trim(strtr($template, $replacements));
    }

    /**
     * A sample render for the compose screen preview.
     */
    public static function previewSample(): array
    {
        return [
            'name'                  => 'Jane Wanjiku',
            'aspirant_profile_link' => url('/aspirants/jane-wanjiku'),
            'county'                => 'Nairobi',
            'constituency'          => 'Dagoretti North',
            'ward'                  => 'Kileleshwa',
            'position'              => 'Member of County Assembly',
            'party'                 => 'ODM',
            'email'                 => 'jane@example.com',
            'phone'                 => '+254712000000',
        ];
    }
}
