<?php

namespace App\Services;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;
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
        $user = $this->userRepository->create($data);

        event(new Registered($user));

        Auth::login($user);

        return $user;
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
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::reset(
            $credentials,
            function (User $user) use ($credentials) {
                $user->forceFill([
                    'password' => Hash::make($credentials['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status;
    }

    /**
     * Log out the user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
