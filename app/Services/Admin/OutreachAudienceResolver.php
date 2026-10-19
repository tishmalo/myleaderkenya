<?php

namespace App\Services\Admin;

use App\Models\Candidate;
use App\Models\EventRegistration;
use App\Models\PoliticalParty;
use App\Models\CandidateSupportContact;
use App\Models\User;
use App\Support\OutreachAudience;
use App\Support\OutreachRecipientData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Turns an audience key into the actual people to message, with the values their
 * {placeholders} take.
 */
class OutreachAudienceResolver
{
    /**
     * Position name fragments per position group, matched against the positions
     * table so a renamed position still lands in the right audience.
     *
     * @var array<string, list<array{0: string, 1: string}>>
     */
    private const POSITION_GROUPS = [
        // [operator, value] where operator is 'like' or 'exact'
        'all_governors'  => [['like', '%governor%']],
        'all_senators'   => [['like', '%senator%']],
        'all_women_reps' => [['like', '%women rep%'], ['like', '%woman rep%'], ['like', '%women representative%'], ['like', '%woman representative%']],
        'all_mps'        => [['exact', 'mp'], ['like', 'mp %'], ['like', '%member of parliament%']],
        'all_mcas'       => [['exact', 'mca'], ['like', 'mca %'], ['like', '%member of county assembly%']],
    ];

    /**
     * @return Collection<int, OutreachRecipientData>
     */
    public function resolve(string $audience): Collection
    {
        return match ($audience) {
            'all_aspirants'         => $this->fromCandidates($this->approvedCandidates()),
            'all_governors'         => $this->fromCandidates($this->approvedCandidatesFor('all_governors')),
            'all_senators'          => $this->fromCandidates($this->approvedCandidatesFor('all_senators')),
            'all_women_reps'        => $this->fromCandidates($this->approvedCandidatesFor('all_women_reps')),
            'all_mps'               => $this->fromCandidates($this->approvedCandidatesFor('all_mps')),
            'all_mcas'              => $this->fromCandidates($this->approvedCandidatesFor('all_mcas')),
            'all_voters'            => $this->fromUsers($this->users(fn (Builder $query) => $query->where(function (Builder $inner): void {
                $inner->where('is_voter', true)->orWhere('is_registered', true);
            }))),
            'registered_voters'     => $this->fromUsers($this->users(fn (Builder $query) => $query->where('is_registered', true))),
            'non_registered_voters' => $this->fromUsers($this->users(fn (Builder $query) => $query->where(function (Builder $inner): void {
                $inner->where('is_voter', false)->orWhereNull('is_voter');
            })->where(function (Builder $inner): void {
                $inner->where('is_registered', false)->orWhereNull('is_registered');
            }))),
            'all_parties'           => $this->fromParties(),
            'all_event_attendees'   => $this->fromEventAttendees(),
            'all_support_groups'    => $this->fromSupportContacts(),
            'all_admins'            => $this->fromUsers($this->admins()),
            default                 => collect(),
        };
    }

    /**
     * A quick count for the compose screen, without materialising everyone.
     */
    public function count(string $audience): int
    {
        return $this->resolve($audience)->count();
    }

    private function approvedCandidates(): Collection
    {
        return Candidate::query()
            ->with(['position:id,name', 'politicalParty:id,name,abbreviation'])
            ->where('approval_status', 'approved')
            ->get();
    }

    private function approvedCandidatesFor(string $group): Collection
    {
        $patterns = self::POSITION_GROUPS[$group] ?? [];

        return Candidate::query()
            ->with(['position:id,name', 'politicalParty:id,name,abbreviation'])
            ->where('approval_status', 'approved')
            ->whereHas('position', function (Builder $query) use ($patterns): void {
                $query->where(function (Builder $inner) use ($patterns): void {
                    foreach ($patterns as [$operator, $value]) {
                        if ($operator === 'exact') {
                            $inner->orWhereRaw('LOWER(name) = ?', [$value]);
                        } else {
                            $inner->orWhereRaw('LOWER(name) LIKE ?', [$value]);
                        }
                    }
                });
            })
            ->get();
    }

    private function users(?callable $scope = null): Collection
    {
        $query = User::query();

        if ($scope !== null) {
            $scope($query);
        }

        return $query->get();
    }

    private function admins(): Collection
    {
        return User::query()
            ->where(function (Builder $query): void {
                $query->whereHas('role', fn (Builder $role) => $role->where('grants_admin_access', true))
                    ->orWhere('role', 'admin');
            })
            ->get();
    }

    /**
     * @param  Collection<int, Candidate>  $candidates
     * @return Collection<int, OutreachRecipientData>
     */
    private function fromCandidates(Collection $candidates): Collection
    {
        return $candidates->map(function (Candidate $candidate): OutreachRecipientData {
            return new OutreachRecipientData(
                name: $candidate->name,
                email: $candidate->email,
                phone: $candidate->phone,
                variables: [
                    'name'                  => (string) $candidate->name,
                    'aspirant_profile_link' => route('aspirants.show', $candidate),
                    'county'                => (string) ($candidate->county ?? ''),
                    'constituency'          => (string) ($candidate->constituency ?? ''),
                    'ward'                  => (string) ($candidate->ward ?? ''),
                    'position'              => (string) ($candidate->position?->name ?? ''),
                    'party'                 => (string) ($candidate->politicalParty?->abbreviation ?: $candidate->politicalParty?->name ?: ''),
                ],
                morphType: $candidate->getMorphClass(),
                morphId: (int) $candidate->getKey(),
            );
        });
    }

    /**
     * @param  Collection<int, User>  $users
     * @return Collection<int, OutreachRecipientData>
     */
    private function fromUsers(Collection $users): Collection
    {
        // A user may own an aspirant profile; borrow its position and party.
        $profiles = Candidate::query()
            ->with(['position:id,name', 'politicalParty:id,name,abbreviation'])
            ->whereIn('user_id', $users->pluck('id')->all())
            ->get()
            ->keyBy('user_id');

        return $users->map(function (User $user) use ($profiles): OutreachRecipientData {
            $profile = $profiles->get($user->getKey());

            return new OutreachRecipientData(
                name: $user->name,
                email: $user->email,
                phone: $user->phone,
                variables: [
                    'name'                  => (string) $user->name,
                    'aspirant_profile_link' => $profile ? route('aspirants.show', $profile) : '',
                    'county'                => (string) ($user->county ?? $profile?->county ?? ''),
                    'constituency'          => (string) ($user->constituency ?? $profile?->constituency ?? ''),
                    'ward'                  => (string) ($user->ward ?? $profile?->ward ?? ''),
                    'position'              => (string) ($profile?->position?->name ?? ''),
                    'party'                 => (string) ($profile?->politicalParty?->abbreviation ?: $profile?->politicalParty?->name ?: ''),
                ],
                morphType: $user->getMorphClass(),
                morphId: (int) $user->getKey(),
            );
        });
    }

    /**
     * @return Collection<int, OutreachRecipientData>
     */
    private function fromParties(): Collection
    {
        $columns = ['id', 'name'];

        foreach (['email_1', 'phone_1'] as $column) {
            if (Schema::hasColumn('political_parties', $column)) {
                $columns[] = $column;
            }
        }

        return PoliticalParty::query()
            ->where('status', 'published')
            ->get($columns)
            ->map(fn (PoliticalParty $party): OutreachRecipientData => new OutreachRecipientData(
                name: $party->name,
                email: $party->email_1 ?? null,
                phone: $party->phone_1 ?? null,
                variables: [
                    'name'                  => (string) $party->name,
                    'aspirant_profile_link' => '',
                    'county'                => '',
                    'constituency'          => '',
                    'ward'                  => '',
                    'position'              => '',
                    'party'                 => (string) ($party->abbreviation ?: $party->name),
                ],
                morphType: $party->getMorphClass(),
                morphId: (int) $party->getKey(),
            ));
    }

    /**
     * @return Collection<int, OutreachRecipientData>
     */
    private function fromEventAttendees(): Collection
    {
        return EventRegistration::query()
            ->latest()
            ->get(['id', 'name', 'email', 'phone'])
            ->map(fn (EventRegistration $registration): OutreachRecipientData => new OutreachRecipientData(
                name: $registration->name,
                email: $registration->email,
                phone: $registration->phone,
                variables: [
                    'name'                  => (string) $registration->name,
                    'aspirant_profile_link' => '',
                    'county'                => '',
                    'constituency'          => '',
                    'ward'                  => '',
                    'position'              => '',
                    'party'                 => '',
                ],
                morphType: $registration->getMorphClass(),
                morphId: (int) $registration->getKey(),
            ));
    }

    /**
     * @return Collection<int, OutreachRecipientData>
     */
    private function fromSupportContacts(): Collection
    {
        return CandidateSupportContact::query()
            ->with('groupType:id,name')
            ->latest()
            ->get()
            ->map(fn (CandidateSupportContact $contact): OutreachRecipientData => new OutreachRecipientData(
                name: $contact->name,
                email: $contact->email,
                phone: $contact->phone,
                variables: [
                    'name'                  => (string) $contact->name,
                    'aspirant_profile_link' => '',
                    'county'                => '',
                    'constituency'          => '',
                    'ward'                  => '',
                    'position'              => '',
                    'party'                 => (string) ($contact->groupType?->name ?? ''),
                ],
                morphType: $contact->getMorphClass(),
                morphId: (int) $contact->getKey(),
            ));
    }

    /**
     * @return array<string, string>
     */
    public function describe(): array
    {
        return OutreachAudience::labels();
    }

    public function title(string $audience): string
    {
        return Str::headline(OutreachAudience::label($audience));
    }
}
