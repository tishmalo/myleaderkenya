<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);
        $token = $user->createToken('voter-app-token')->plainTextToken;

        return [
            'message' => 'User registered successfully',
            'user'    => $this->userPayload($user),
            'token'   => $token,
        ];
    }

    public function login(string $username, string $password): array
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid username or password', 401);
        }

        // Revoke all previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('voter-app-token')->plainTextToken;

        return [
            'message'   => 'Login successful',
            'id'        => $user->id,
            'username'  => $user->username,
            'user_type' => $user->user_type,
            'token'     => $token,
        ];
    }

    public function refreshToken(User $user): array
    {
        // Revoke all old tokens
        $user->tokens()->delete();

        // Create new token
        $newToken = $user->createToken('voter-app-token')->plainTextToken;

        return [
            'message' => 'Token refreshed successfully',
            'token'   => $newToken,
        ];
    }

    public function updateProfile(User $user, array $data): array
    {
        $this->userRepository->update($user, $data);

        return [
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => $this->userPayload($user)
        ];
    }

    public function getProfile(User $user): array
    {
        return [
            'user' => $this->userPayload($user)
        ];
    }

    private function userPayload(User $user): array
    {
        return array_merge($user->only([
            'id', 'username', 'name', 'email', 'phone', 'gender', 'year_of_birth',
            'county', 'constituency', 'ward', 'polling_station',
            'country_of_residence', 'is_voter', 'is_registered'
        ]), [
            'user_type' => $user->user_type,
        ]);
    }
}
