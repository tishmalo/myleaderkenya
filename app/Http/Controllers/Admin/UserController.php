<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use App\Services\Admin\CountyService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private CountyService $countyService
    ) {}

    /**
     * Display a listing of users (excluding admin)
     */
    public function index()
    {
        $data = $this->userService->getUsersList(request()->only(['search', 'county', 'status']));

        $users = $data['users'];
        $counties = $data['counties'];

        return view('users.index', compact('users', 'counties'));
    }

    /**
     * Show the form for creating a new user
     */

    public function create()
    {
        $counties = $this->countyService->getAllCounties();

        return view('users.create', compact('counties'));
    }
    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {

        $this->userService->createUser($request->validated());

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
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($user->username === 'admin') {
            abort(403, 'You cannot edit the admin account.');
        }

        $this->userService->updateUser($user, $request->only([
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

        $this->userService->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }
}