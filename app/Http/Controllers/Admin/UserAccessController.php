<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserAccessAdminRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(): View
    {
        $roles = Role::query()
            ->whereIn('name', [Role::USER, Role::ADMIN, Role::SUPERADMIN])
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, [Role::USER, Role::ADMIN, Role::SUPERADMIN], true));

        $admins = User::query()
            ->with('role')
            ->where(function ($query): void {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('name', [Role::ADMIN, Role::SUPERADMIN]))
                    ->orWhere('role', 'admin');
            })
            ->latest()
            ->get();

        $users = User::query()
            ->with('role')
            ->where('username', '!=', 'admin')
            ->latest()
            ->paginate(20);

        return view('admin.user-access.index', compact('admins', 'roles', 'users'));
    }

    public function store(StoreUserAccessAdminRequest $request): RedirectResponse
    {
        $adminRoleId = Role::idFor(Role::ADMIN);

        $data = $request->validated();
        $data['role_id'] = $adminRoleId;

        $this->userService->createUser($data);

        return redirect()
            ->route('user-access.index')
            ->with('success', 'Admin created successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);

        if ($request->user()->is($user) && $user->isSuperAdmin() && $role->name !== Role::SUPERADMIN) {
            return back()->with('error', 'You cannot remove your own super admin access.');
        }

        $user->forceFill([
            'role_id' => $role->id,
            'role' => in_array($role->name, [Role::ADMIN, Role::SUPERADMIN], true) ? 'admin' : 'user',
        ])->save();

        return back()->with('success', 'User role updated successfully.');
    }
}
