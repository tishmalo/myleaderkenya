<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        $candidateIds = $data['candidate_ids'] ?? null;
        $data['relationship'] = $this->relationshipFrom($data);
        unset($data['candidate_ids'], $data['user_type']);

        $data['password'] = Hash::make($data['password']);
        $data['email'] = $data['email'] ?? $data['username'] . '@regista.local';
        $data['is_voter'] = $data['is_voter'] ?? false;

        $user = User::create($data);
        $this->syncCandidateRelationships($user, $candidateIds, $data['relationship'] ?? null);

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function update(User $user, array $data): bool
    {
        $candidateIds = $data['candidate_ids'] ?? null;
        $hasRelationshipInput = array_key_exists('relationship', $data) || array_key_exists('user_type', $data);

        if ($hasRelationshipInput) {
            $data['relationship'] = $this->relationshipFrom($data);
        }

        unset($data['candidate_ids'], $data['user_type']);

        $updated = $user->update($data);
        $this->syncCandidateRelationships($user, $candidateIds, $hasRelationshipInput ? $data['relationship'] : $user->relationship);

        return $updated;
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->update([
            'password' => Hash::make($password),
        ]);
    }

    public function getAllVoters(): Collection
    {
        return User::select('username', 'county', 'age', 'gender', 'is_voter', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getVoterStats(): array
    {
        return [
            'confirmedVoters' => User::where('is_voter', true)->count(),
            'avgAge' => round(User::whereNotNull('age')->avg('age') ?? 0, 1),
            'byCounty' => User::select('county')
                ->selectRaw('COUNT(*) as count')
                ->where('is_voter', true)
                ->groupBy('county')
                ->get(),
        ];
    }

    public function count(): int
    {
        return User::count();
    }

    public function countVoters(): int
    {
        return User::where('is_voter', true)->count();
    }

    private function relationshipFrom(array $data): ?string
    {
        $relationship = $data['relationship'] ?? $data['user_type'] ?? null;

        if ($relationship === null || $relationship === '') {
            return null;
        }

        return trim((string) $relationship);
    }

    private function syncCandidateRelationships(User $user, ?array $candidateIds, ?string $relationship): void
    {
        if ($candidateIds === null) {
            return;
        }

        $syncData = collect($candidateIds)
            ->unique()
            ->mapWithKeys(fn (int $candidateId) => [
                $candidateId => ['relationship' => $relationship],
            ])
            ->all();

        $user->relatedCandidates()->sync($syncData);
    }
}
