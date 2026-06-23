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
        $data['password'] = Hash::make($data['password']);
        $data['email'] = $data['email'] ?? $data['username'] . '@regista.local';
        $data['is_voter'] = $data['is_voter'] ?? false;

        return User::create($data);
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
        return $user->update($data);
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
}
