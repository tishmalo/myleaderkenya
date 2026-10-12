<?php

namespace App\Repositories\Admin;

use App\Contracts\Repositories\Admin\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function getFilteredUsersPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = User::with('role')->where('username', '!=', 'admin');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $normalized = strtolower($search);
            $hashes = array_values(array_unique([
                hash('sha256', $normalized),
                User::piiHash($normalized),
            ]));

            $query->where(function ($q) use ($search, $hashes) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereIn('email_hash', $hashes)
                    ->orWhereIn('phone_hash', $hashes)
                    ->orWhereIn('id_number_hash', $hashes);

                if (ctype_digit($search)) {
                    $q->orWhereKey((int) $search);
                }
            });
        }

        if (! empty($filters['county'])) {
            $query->where('county', $filters['county']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'registered') {
                $query->where(function ($q) {
                    $q->where('is_voter', 1)->orWhere('is_registered', 1);
                });
            } else {
                $query->where('is_voter', 0)->where('is_registered', 0);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function getRoleAllocationUsersPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = User::with('role')->where('username', '!=', 'admin');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $term = '%'.$search.'%';
            $normalized = strtolower($search);

            // Emails are encrypted at rest, so they can only be matched through their hash.
            $hashes = array_values(array_unique([
                hash('sha256', $normalized),
                User::piiHash($normalized),
            ]));

            $query->where(function ($q) use ($term, $hashes) {
                $q->where('name', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhereIn('email_hash', $hashes);
            });
        }

        if (! empty($filters['role_filter'])) {
            $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', $filters['role_filter']));
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getAdminUsers(): Collection
    {
        return User::with('role')
            ->where(function ($query): void {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('grants_admin_access', true))
                    ->orWhere('role', 'admin');
            })
            ->latest()
            ->get();
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

    public function findByEmailHash(string $emailHash): ?User
    {
        return User::where('email_hash', $emailHash)->first();
    }

    public function usernameExists(string $username): bool
    {
        return User::where('username', $username)->exists();
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
