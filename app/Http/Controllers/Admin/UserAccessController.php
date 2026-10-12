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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()->with('permissions')->ordered()->get();
        $staffRoles = $roles->where('grants_admin_access', true)->values();
        $editableRoles = $roles->where('has_full_access', false)->values();

        // The permissions tab works on one role at a time.
        $selectedRole = $editableRoles->firstWhere('id', (int) $request->query('role'))
            ?? $editableRoles->first();

        $permissions = Permission::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $admins = User::query()
            ->with('role')
            ->where(function ($query): void {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('grants_admin_access', true))
                    ->orWhere('role', 'admin');
            })
            ->latest()
            ->get();

        // Role Allocation filters (name/username/email search + role filter).
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role_filter', '');

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

        return view('admin.user-access.index', compact(
            'admins',
            'editableRoles',
            'permissions',
            'roleFilter',
            'roles',
            'search',
            'selectedRole',
            'staffRoles',
            'users',
        ));
    }

    public function store(StoreUserAccessAdminRequest $request): RedirectResponse
    {
        Gate::authorize('createAdmin', Role::class);

        $data = $request->validated();
        $data['role_id'] = $data['role_id'] ?? Role::idFor(Role::ADMIN);

        $role = Role::query()->find($data['role_id']);

        $this->userService->createUser($data);

        return redirect()
            ->route('user-access.index')
            ->with('success', ($role?->label ?? 'Admin').' account created successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('assignRole', Role::class);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);

        if ($request->user()->is($user) && $user->isSuperAdmin() && ! $role->has_full_access) {
            return $this->backToAllocation()->with('error', 'You cannot remove your own super admin access.');
        }

        if ($user->isSuperAdmin() && ! $role->has_full_access && $this->isLastFullAccessUser($user)) {
            return $this->backToAllocation()->with('error', 'At least one account must keep full access.');
        }

        $user->forceFill([
            'role_id' => $role->id,
            'role' => $role->legacyUserRole(),
        ])->save();

        return $this->backToAllocation()->with('success', 'User role updated successfully.');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('managePermissions', Role::class);

        if ($role->has_full_access) {
            return $this->backToPermissions($role)->with('error', $role->label.' keeps all permissions by default.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return $this->backToPermissions($role)->with('success', $role->label.' permissions updated successfully.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:60', 'alpha_dash', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'grants_admin_access' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $role = Role::query()->create([
            'name' => $this->uniqueName($validated['name'] ?? null, $validated['label']),
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'grants_admin_access' => $request->boolean('grants_admin_access'),
            'has_full_access' => false,
            'is_system' => false,
            'sort_order' => $validated['sort_order'] ?? 100,
        ]);

        return $this->backToRoles()->with('success', $role->label.' created. Set its permissions next.');
    }

    public function updateRoleSettings(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'grants_admin_access' => ['nullable', 'boolean'],
            'has_full_access' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $grantsAdminAccess = $request->boolean('grants_admin_access');
        $hasFullAccess = $request->boolean('has_full_access');

        // A full access role must also be able to reach the admin area.
        if ($hasFullAccess) {
            $grantsAdminAccess = true;
        }

        if ($role->isSystem() && $role->has_full_access && ! $hasFullAccess) {
            return $this->backToRoles()->with('error', $role->label.' must keep full access.');
        }

        if ($hasFullAccess && ! $role->has_full_access && ! $request->user()->isSuperAdmin()) {
            return $this->backToRoles()->with('error', 'Only a super admin can grant full access.');
        }

        if ($role->grants_admin_access && ! $grantsAdminAccess && $role->users()->exists()) {
            return $this->backToRoles()->with('error', 'Move the accounts on '.$role->label.' to another role before removing its admin access.');
        }

        $role->fill([
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'grants_admin_access' => $grantsAdminAccess,
            'has_full_access' => $hasFullAccess,
            'sort_order' => $validated['sort_order'] ?? $role->sort_order,
        ]);

        if (! $role->isSystem()) {
            $role->name = $this->uniqueName($request->string('name')->toString() ?: null, $validated['label'], $role);
        }

        $role->save();

        // Keep the legacy users.role column in step with the role flag.
        $role->users()->update(['role' => $role->legacyUserRole()]);

        return $this->backToRoles()->with('success', $role->label.' updated successfully.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        if ($role->isSystem()) {
            return $this->backToRoles()->with('error', $role->label.' is a built in role and cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return $this->backToRoles()->with('error', 'Move the accounts on '.$role->label.' to another role first.');
        }

        $label = $role->label;

        $role->permissions()->detach();
        $role->delete();

        return $this->backToRoles()->with('success', $label.' deleted.');
    }

    /**
     * Return to the Role Allocation tab, keeping the active search/role filters.
     */
    private function backToAllocation(): RedirectResponse
    {
        $previous = explode('#', url()->previous() ?: route('user-access.index'))[0];

        return redirect()->to($previous.'#allocation');
    }

    private function backToRoles(): RedirectResponse
    {
        return redirect()->to(route('user-access.index').'#roles');
    }

    private function backToPermissions(Role $role): RedirectResponse
    {
        return redirect()->to(route('user-access.index', ['role' => $role->id]).'#permissions');
    }

    private function isLastFullAccessUser(User $user): bool
    {
        return ! User::query()
            ->whereKeyNot($user->getKey())
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('has_full_access', true))
            ->exists();
    }

    /**
     * Build a unique, url friendly role name from a label when none is given.
     */
    private function uniqueName(?string $name, string $label, ?Role $ignore = null): string
    {
        $base = Str::slug($name ?: $label, '_') ?: 'role';
        $candidate = $base;
        $suffix = 2;

        while (Role::query()
            ->where('name', $candidate)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $candidate = $base.'_'.$suffix++;
        }

        return $candidate;
    }
}
