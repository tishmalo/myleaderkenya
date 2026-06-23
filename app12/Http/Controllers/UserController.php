<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users (excluding admin)
     */
    public function index()
    {
        $query = User::where('username', '!=', 'admin');

        // Search
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by County
        if (request()->filled('county')) {
            $query->where('county', request('county'));
        }

        // Filter by Voter Status
        if (request()->filled('status')) {
            if (request('status') === 'registered') {
                $query->where(function($q) {
                    $q->where('is_voter', 1)->orWhere('is_registered', 1);
                });
            } else {
                $query->where('is_voter', 0)->where('is_registered', 0);
            }
        }

        $users = $query->latest()->paginate(20);

        $counties = User::whereNotNull('county')
                        ->distinct()
                        ->pluck('county')
                        ->sort();

        return view('users.index', compact('users', 'counties'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:255|unique:users,username',
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'gender'       => 'nullable|in:male,female,other',
            'year_of_birth'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'county'       => 'nullable|string|max:100',
            'constituency' => 'nullable|string|max:100',
            'ward'         => 'nullable|string|max:100',
            'polling_station' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:100',
            'password'     => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'username'       => $request->username,
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'gender'         => $request->gender,
            'year_of_birth'  => $request->year_of_birth,
            'county'         => $request->county,
            'constituency'   => $request->constituency,
            'ward'           => $request->ward,
            'polling_station'=> $request->polling_station,
            'country_of_residence' => $request->country_of_residence ?? 'Kenya',
            'password'       => Hash::make($request->password),
            'is_voter'       => false,
            'is_registered'  => false,
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'User created successfully!');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        if ($user->username === 'admin') {
            abort(403, 'You cannot edit the admin account.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        if ($user->username === 'admin') {
            abort(403, 'You cannot edit the admin account.');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:20',
            'gender'       => 'nullable|in:male,female,other',
            'year_of_birth'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'county'       => 'nullable|string|max:100',
            'constituency' => 'nullable|string|max:100',
            'ward'         => 'nullable|string|max:100',
            'polling_station' => 'nullable|string|max:255',
            'country_of_residence' => 'nullable|string|max:100',
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'gender', 'year_of_birth',
            'county', 'constituency', 'ward', 'polling_station', 'country_of_residence'
        ]));

        return redirect()->route('users.index')
                         ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->username === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete the admin account.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }
}