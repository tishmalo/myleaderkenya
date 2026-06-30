<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);
        $verification = $this->sendEmailVerificationCode($user);
        $token = $user->createToken('voter-app-token')->plainTextToken;

        return [
            'message' => 'User registered successfully',
            'user'    => $this->userPayload($user->fresh()),
            'token'   => $token,
            'email_verification' => $verification,
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
            'email_verified' => !is_null($user->email_verified_at),
            'email_verified_at' => $user->email_verified_at,
            'token'     => $token,
        ];
    }

    public function verifyEmail(string $email, string $otp): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        if ($user->email_verified_at) {
            return [
                'message' => 'Email is already verified',
                'user'    => $this->userPayload($user),
            ];
        }

        if (!$user->otp || !$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            throw new \Exception('Verification code has expired', 422);
        }

        if (!Hash::check($otp, $user->otp)) {
            throw new \Exception('Invalid verification code', 422);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        return [
            'message' => 'Email verified successfully',
            'user'    => $this->userPayload($user->fresh()),
        ];
    }

    public function resendEmailVerification(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        return [
            'message' => $user->email_verified_at
                ? 'Email is already verified'
                : 'Verification code sent',
            'email_verification' => $this->sendEmailVerificationCode($user),
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

    private function sendEmailVerificationCode(User $user): array
    {
        if ($user->email_verified_at) {
            return [
                'required' => false,
                'sent' => false,
                'verified' => true,
            ];
        }

        if (empty($user->email) || str_ends_with($user->email, '@regista.local')) {
            return [
                'required' => false,
                'sent' => false,
                'verified' => false,
                'reason' => 'No real email address is available for this user.',
            ];
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        $user->forceFill([
            'otp' => Hash::make($otp),
            'otp_expires_at' => $expiresAt,
        ])->save();

        try {
            Mail::raw(
                "Your verification code is {$otp}. It expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Verify your email');
                }
            );
        } catch (\Throwable $e) {
            return [
                'required' => true,
                'sent' => false,
                'verified' => false,
                'reason' => 'Verification code could not be sent.',
            ];
        }

        return [
            'required' => true,
            'sent' => true,
            'verified' => false,
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    private function userPayload(User $user): array
    {
        return array_merge($user->only([
            'id', 'username', 'name', 'email', 'phone', 'gender', 'year_of_birth',
            'county', 'constituency', 'ward', 'polling_station',
            'country_of_residence', 'is_voter', 'is_registered', 'email_verified_at'
        ]), [
            'user_type' => $user->user_type,
            'email_verified' => !is_null($user->email_verified_at),
        ]);
    }
}
