<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserAccessAdminRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function __construct(
        private UserAccessService $userAccess
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Role::class);

        return view('admin.user-access.index', $this->userAccess->getIndexData(
            $request->only(['search', 'role_filter']),
            $request->query('role') !== null ? (int) $request->query('role') : null,
        ));
    }

    public function store(StoreUserAccessAdminRequest $request): RedirectResponse
    {
        Gate::authorize('createAdmin', Role::class);

        $result = $this->userAccess->createAdmin($request->validated());

        return redirect()
            ->route('user-access.index')
            ->with($result['status'], $result['message']);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('assignRole', Role::class);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $result = $this->userAccess->updateUserRole($user, (int) $validated['role_id'], $request->user());

        return $this->backToAllocation()->with($result['status'], $result['message']);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('managePermissions', Role::class);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $result = $this->userAccess->updatePermissions($role, $validated['permissions'] ?? []);

        return $this->backToPermissions($role)->with($result['status'], $result['message']);
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

        $result = $this->userAccess->createRole($validated);

        return $this->backToRoles()->with($result['status'], $result['message']);
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

        $validated['name'] = $request->string('name')->toString() ?: null;

        $result = $this->userAccess->updateRoleSettings($role, $validated, $request->user());

        return $this->backToRoles()->with($result['status'], $result['message']);
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        $result = $this->userAccess->deleteRole($role);

        return $this->backToRoles()->with($result['status'], $result['message']);
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
}
