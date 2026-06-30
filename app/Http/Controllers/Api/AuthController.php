<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\RefreshRequest;
use App\Http\Requests\Api\LoginRequest;
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
        try {
            $result = $this->authService->refreshToken($request->user());

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

    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        try {
            $result = $this->authService->verifyEmail($validated['email'], $validated['otp']);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function resendEmailVerification(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $result = $this->authService->resendEmailVerification($validated['email']);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
