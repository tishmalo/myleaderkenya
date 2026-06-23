<?php

namespace App\Contracts\Repositories\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getFilteredUsersPaginated(array $filters, int $perPage): LengthAwarePaginator;
    
    public function getDistinctCounties(): \Illuminate\Support\Collection;
    
    public function createUser(array $data): User;
    
    public function updateUser(User $user, array $data): bool;
    
    public function saveUser(User $user): bool;
    
    public function deleteUser(User $user): bool;
    
    public function getUsersWithLocationPaginated(int $perPage): LengthAwarePaginator;
    
    public function getUsersWithLocation(): Collection;
}
