<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'username'       => 'required|string|max:255|unique:users,username',
        'name'           => 'required|string|max:255',                    // Full name
        'email'          => 'nullable|email|unique:users,email',
        'phone'          => 'nullable|string|max:20',
        'gender'         => 'nullable|in:male,female,other',
        'year_of_birth'  => 'nullable|integer|min:1900|max:' . date('Y'),
        'county'         => 'nullable|string|max:100',
        'constituency'   => 'nullable|string|max:100',
        'ward'           => 'nullable|string|max:100',
        'polling_station'=> 'nullable|string|max:255',
        'country_of_residence' => 'nullable|string|max:100',
        'password'       => ['required', 'confirmed', Password::defaults()],
    ]);

    $user = User::create([
        'username'       => $request->username,
        'name'           => $request->name,
        'email'          => $request->email ?? $request->username . '@regista.local',
        'phone'          => $request->phone,
        'gender'         => $request->gender,
        'year_of_birth'  => $request->year_of_birth,
        'county'         => $request->county,
        'constituency'   => $request->constituency,
        'ward'           => $request->ward,
        'polling_station'=> $request->polling_station,
        'country_of_residence' => $request->country_of_residence,
        'password'       => Hash::make($request->password),
        'is_voter'       => true,
    ]);

    $token = $user->createToken('voter-app-token')->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'user'    => $user->only(['username', 'name', 'email', 'phone', 'gender', 'year_of_birth', 'county', 'constituency', 'ward', 'polling_station', 'country_of_residence', 'is_voter', 'is_registered']),
        'token'   => $token,
    ], 201);
}
    /**
     * Refresh token (using username + current token)
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $user = User::where('username', $request->username)->first();

        // Revoke all old tokens
        $user->tokens()->delete();

        // Create new token
        $newToken = $user->createToken('voter-app-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token'   => $newToken,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Revoke all previous tokens (optional - forces single device login)
        $user->tokens()->delete();

        $token = $user->createToken('voter-app-token')->plainTextToken;

        return response()->json([
            'message'  => 'Login successful',
            'username' => $user->username,
            'token'    => $token,
        ], 200);
    }

    public function profile(Request $request)
    {

        $user = $request->user();

        return response()->json([
            'user' => $user->only([
                'username', 'name', 'email', 'phone', 'gender',
                'year_of_birth', 'county', 'constituency', 'ward',
                'polling_station', 'country_of_residence', 'is_voter', 'is_registered'
            ])
        ]);
    }

    public function updateProfile(Request $request)
{
    $user = $request->user();

    $request->validate([
        'gender'          => 'nullable|in:male,female,other',
        'year_of_birth'   => 'nullable|integer|min:1900|max:' . date('Y'),
        'county'          => 'nullable|string|max:100',
        'constituency'    => 'nullable|string|max:100',
        'ward'            => 'nullable|string|max:100',
        'polling_station' => 'nullable|string|max:255',
        'phone'           => 'nullable|string|max:20',
        'is_voter'        => 'boolean',
        'country_of_residence' => 'nullable|string|max:100',
    ]);

    $user->update([
        'gender'          => $request->gender,
        'year_of_birth'   => $request->year_of_birth,
        'county'          => $request->county,
        'constituency'    => $request->constituency,
        'ward'            => $request->ward,
        'polling_station' => $request->polling_station,
        'phone'           => $request->phone,
        'is_voter'        => $request->boolean('is_voter', $user->is_voter),
        'is_registered'   => $request->boolean('is_voter', $user->is_registered),
        'country_of_residence' => $request->country_of_residence,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user'    => $user->only(['username','name','email','phone','gender','year_of_birth','county','constituency','ward','polling_station','country_of_residence','is_voter','is_registered'])
    ]);
}
}