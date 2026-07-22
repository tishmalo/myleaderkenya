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
        $data['role_id'] = $data['role_id'] ?? Role::idFor(Role::USER);

        $roleName = $data['role_id'] ? Role::query()->whereKey($data['role_id'])->value('name') : Role::USER;
        $data['role'] = in_array($roleName, [Role::ADMIN, Role::SUPERADMIN], true) ? 'admin' : 'user';

        return $this->userRepository->createUser($data);
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
