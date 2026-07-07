<?php

namespace App\Services;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user and log them in.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $candidateData = collect($data)->only([
                'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id',
                'about', 'country', 'county', 'constituency', 'ward',
            ])->all();

            $user = $this->userRepository->create(collect($data)->only([
                'name', 'email', 'phone', 'password', 'is_aspirant',
            ])->all());

            if (! empty($candidateData['position_id'])) {
                $candidateData['country'] = $candidateData['country'] ?? 'Kenya';
                $candidateData['approval_status'] = 'pending';

                if (! Schema::hasColumn('candidates', 'approval_status')) {
                    unset($candidateData['approval_status']);
                }

                Candidate::create($candidateData);
            }

            event(new Registered($user));
            Auth::login($user);

            return $user;
        });
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password): bool
    {
        return $this->userRepository->updatePassword($user, $password);
    }

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(string $email): string
    {
        $user = User::findByEmailValue($email);

        if (! $user) {
            return Password::INVALID_USER;
        }

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        return Password::RESET_LINK_SENT;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(array $credentials): string
    {
        $user = User::findByEmailValue((string) ($credentials['email'] ?? ''));

        if (! $user) {
            return Password::INVALID_USER;
        }

        if (! Password::broker()->tokenExists($user, (string) ($credentials['token'] ?? ''))) {
            return Password::INVALID_TOKEN;
        }

        $user->forceFill([
            'password' => Hash::make($credentials['password']),
            'remember_token' => Str::random(60),
        ])->save();

        Password::broker()->deleteToken($user);
        event(new PasswordReset($user));

        return Password::PASSWORD_RESET;
    }

    /**
     * Log out the user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}

