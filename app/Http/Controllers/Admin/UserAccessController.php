<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\StoreUserAccessAdminRequest;
use App\Http\Requests\Admin\UpdateRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleSettingsRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
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

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('assignRole', Role::class);

        $result = $this->userAccess->updateUserRole($user, (int) $request->validated('role_id'), $request->user());

        return $this->backToAllocation()->with($result['status'], $result['message']);
    }

    public function updatePermissions(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('managePermissions', Role::class);

        $result = $this->userAccess->updatePermissions($role, $request->validated('permissions') ?? []);

        return $this->backToPermissions($role)->with($result['status'], $result['message']);
    }

    public function storeRole(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        $result = $this->userAccess->createRole($request->validated());

        return $this->backToRoles()->with($result['status'], $result['message']);
    }

    public function updateRoleSettings(UpdateRoleSettingsRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('manageRoles', Role::class);

        $validated = $request->validated();
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
