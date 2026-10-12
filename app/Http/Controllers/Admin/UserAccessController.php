<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserAccessAdminRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()
            ->with('permissions')
            ->whereIn('name', [Role::USER, Role::ADMIN, Role::SUPERADMIN])
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, [Role::USER, Role::ADMIN, Role::SUPERADMIN], true));

        $permissionRoles = $roles->reject(fn (Role $role) => $role->name === Role::SUPERADMIN);

        $permissions = Permission::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $admins = User::query()
            ->with('role')
            ->where(function ($query): void {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('name', [Role::ADMIN, Role::SUPERADMIN]))
                    ->orWhere('role', 'admin');
            })
            ->latest()
            ->get();

        // Role Allocation filters (name/username/email search + role filter).
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', '');

        if ($roleFilter !== '' && ! $roles->contains('name', $roleFilter)) {
            $roleFilter = '';
        }

        $users = User::query()
            ->with('role')
            ->where('username', '!=', 'admin')
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $normalized = Str::lower($search);

                // Emails are encrypted at rest, so they can only be matched through their hash.
                $hashes = array_values(array_unique([
                    hash('sha256', $normalized),
                    User::piiHash($normalized),
                ]));

                $query->where(function ($query) use ($term, $hashes): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('username', 'like', $term)
                        ->orWhereIn('email_hash', $hashes);
                });
            })
            ->when($roleFilter !== '', fn ($query) => $query->whereHas(
                'role',
                fn ($roleQuery) => $roleQuery->where('name', $roleFilter)
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.user-access.index', compact('admins', 'permissionRoles', 'permissions', 'roles', 'users', 'search', 'roleFilter'));
    }

    public function store(StoreUserAccessAdminRequest $request): RedirectResponse
    {
        Gate::authorize('createAdmin', Role::class);

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
        Gate::authorize('assignRole', Role::class);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);

        if ($request->user()->is($user) && $user->isSuperAdmin() && $role->name !== Role::SUPERADMIN) {
            return $this->backToAllocation()->with('error', 'You cannot remove your own super admin access.');
        }

        $user->forceFill([
            'role_id' => $role->id,
            'role' => in_array($role->name, [Role::ADMIN, Role::SUPERADMIN], true) ? 'admin' : 'user',
        ])->save();

        return $this->backToAllocation()->with('success', 'User role updated successfully.');
    }

    /**
     * Return to the Role Allocation tab, keeping the active search/role filters.
     */
    private function backToAllocation(): RedirectResponse
    {
        $previous = explode('#', url()->previous() ?: route('user-access.index'))[0];

        return redirect()->to($previous.'#allocation');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('managePermissions', Role::class);

        if ($role->name === Role::SUPERADMIN) {
            return back()->with('error', 'Super admin keeps all permissions by default.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
