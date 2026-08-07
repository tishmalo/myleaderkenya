<?php

namespace App\Services\Web;

use App\Contracts\Repositories\Web\UserProfileRepositoryInterface;
use App\Models\User;

class UserProfileService
{
    public const REQUIRED_FIELDS = [
        'name',
        'email',
        'gender',
        'year_of_birth',
        'county',
        'constituency',
        'ward',
        'country_of_residence',
    ];

    public function __construct(private UserProfileRepositoryInterface $profiles) {}

    public function isComplete(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        foreach (self::REQUIRED_FIELDS as $field) {
            $value = $user->getAttribute($field);

            if ($value === null || (is_string($value) && trim($value) === '')) {
                return false;
            }
        }

        return ! str_ends_with(strtolower((string) $user->email), '@regista.local');
    }

    public function formData(User $user): array
    {
        $county = old('county', $user->county);
        $constituency = old('constituency', $user->constituency);
        $ward = old('ward', $user->ward);

        return [
            'counties' => $this->profiles->counties(),
            'constituencies' => filled($county)
                ? $this->profiles->constituencies($county)
                : collect(),
            'wards' => filled($constituency)
                ? $this->profiles->wards($constituency)
                : collect(),
            'pollingStations' => filled($ward)
                ? $this->profiles->pollingStations($ward)
                : collect(),
            'profileComplete' => $this->isComplete($user),
        ];
    }

    public function update(User $user, array $data): User
    {
        $data['is_voter'] = (bool) ($data['is_voter'] ?? false);
        $this->profiles->update($user, $data);

        return $user->fresh();
    }
}