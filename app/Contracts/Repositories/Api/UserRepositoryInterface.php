<?php

namespace App\Contracts\Repositories\Api;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function update(User $user, array $data): bool;

    public function updatePassword(User $user, string $password): bool;

    public function getAllVoters(): Collection;

    public function getVoterStats(): array;

    public function count(): int;

    public function countVoters(): int;
}
