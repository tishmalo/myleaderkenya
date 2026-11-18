<?php

namespace App\Http\Controllers\Api;
//deploy
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EmailOtpRequest;
use App\Http\Requests\Api\EmailRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\RefreshRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ResetPasswordOtpRequest;
use App\Http\Requests\Api\SavePlayerIdRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Services\Api\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    /**
     * Refresh token (requires authentication)
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid bearer token.'
            ], 401);
        }

        try {
            $result = $this->authService->refreshToken($user);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->username, $request->password);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function profile(Request $request)
    {
        $result = $this->authService->getProfile($request->user());

        return response()->json($result);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $result = $this->authService->updateProfile($request->user(), $request->validated());

        return response()->json($result);
    }

    public function verifyEmail(EmailOtpRequest $request)
    {
        try {
            $result = $this->authService->verifyEmail($request->email(), $request->otp());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function resendEmailVerification(EmailRequest $request)
    {
        try {
            $result = $this->authService->resendEmailVerification($request->email());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
    public function checkEmail(EmailRequest $request)
    {
        try {
            $result = $this->authService->sendPasswordResetOtp($request->email());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function checkOtp(EmailOtpRequest $request)
    {
        try {
            $result = $this->authService->checkPasswordResetOtp($request->email(), $request->otp());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function resetPassword(ResetPasswordOtpRequest $request)
    {
        try {
            $result = $this->authService->resetPasswordWithOtp(
                $request->email(),
                $request->otp(),
                $request->password()
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function savePlayerId(SavePlayerIdRequest $request)
    {
        $result = $this->authService->savePlayerId($request->user(), $request->playerId());

        return response()->json($result, $result['success'] ? 200 : 501);
    }
}
