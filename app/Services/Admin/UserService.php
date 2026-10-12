<?php

namespace App\Services\Admin;

use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUsersList(array $filters): array
    {
        return [
            'users'    => $this->userRepository->getFilteredUsersPaginated($filters, 20),
            'counties' => $this->userRepository->getDistinctCounties(),
        ];
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['is_voter'] = false;
        $data['is_registered'] = false;
        $data['country_of_residence'] = $data['country_of_residence'] ?? 'Kenya';

        $role = $this->resolveRole($data['role_id'] ?? null);

        $data['role_id'] = $role?->id;
        $data['role'] = $role?->legacyUserRole() ?? Role::USER;

        return $this->userRepository->createUser($data);
    }

    private function resolveRole(mixed $roleId): ?Role
    {
        if ($roleId) {
            $role = Role::query()->find($roleId);

            if ($role) {
                return $role;
            }
        }

        return Role::query()->where('name', Role::USER)->first();
    }

    public function updateProfile(User $user, array $data): bool
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        return $this->userRepository->saveUser($user);
    }

    public function updateUser(User $user, array $data): bool
    {
        return $this->userRepository->updateUser($user, $data);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->deleteUser($user);
    }

    public function getUsersWithLocationPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->getUsersWithLocationPaginated($perPage);
    }

    public function getUsersWithLocation()
    {
        return $this->userRepository->getUsersWithLocation();
    }
}
