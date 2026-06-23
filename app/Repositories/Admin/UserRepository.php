<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function getFilteredUsersPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = User::where('username', '!=', 'admin');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['county'])) {
            $query->where('county', $filters['county']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'registered') {
                $query->where(function($q) {
                    $q->where('is_voter', 1)->orWhere('is_registered', 1);
                });
            } else {
                $query->where('is_voter', 0)->where('is_registered', 0);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function getDistinctCounties(): \Illuminate\Support\Collection
    {
        return User::whereNotNull('county')
                   ->distinct()
                   ->pluck('county')
                   ->sort();
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function saveUser(User $user): bool
    {
        return $user->save();
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    public function getUsersWithLocationPaginated(int $perPage): LengthAwarePaginator
    {
        return User::with('location')->latest()->paginate($perPage);
    }

    public function getUsersWithLocation(): Collection
    {
        return User::with('location')->get();
    }
}
