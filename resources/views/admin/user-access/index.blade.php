@extends('layouts.app')

@section('page_title', 'User Access')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" data-user-access-tabs>
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="border-b border-zinc-800 px-4 py-4">
            <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="User access sections">
                <button type="button" data-tab-target="permissions" role="tab" class="user-access-tab whitespace-nowrap px-5 py-3 rounded-2xl text-sm font-semibold text-zinc-400 hover:text-white hover:bg-zinc-800">
                    <i class="fas fa-user-shield mr-2"></i> Role Permissions
                </button>
                @can('manageRoles', \App\Models\Role::class)
                    <button type="button" data-tab-target="roles" role="tab" class="user-access-tab whitespace-nowrap px-5 py-3 rounded-2xl text-sm font-semibold text-zinc-400 hover:text-white hover:bg-zinc-800">
                        <i class="fas fa-user-tag mr-2"></i> Roles
                    </button>
                @endcan
                @can('createAdmin', \App\Models\Role::class)
                    <button type="button" data-tab-target="create-admin" role="tab" class="user-access-tab whitespace-nowrap px-5 py-3 rounded-2xl text-sm font-semibold text-zinc-400 hover:text-white hover:bg-zinc-800">
                        <i class="fas fa-user-plus mr-2"></i> Create Admin
                    </button>
                @endcan
                <button type="button" data-tab-target="admins" role="tab" class="user-access-tab whitespace-nowrap px-5 py-3 rounded-2xl text-sm font-semibold text-zinc-400 hover:text-white hover:bg-zinc-800">
                    <i class="fas fa-users-gear mr-2"></i> Admin Accounts
                </button>
                @can('assignRole', \App\Models\Role::class)
                    <button type="button" data-tab-target="allocation" role="tab" class="user-access-tab whitespace-nowrap px-5 py-3 rounded-2xl text-sm font-semibold text-zinc-400 hover:text-white hover:bg-zinc-800">
                        <i class="fas fa-key mr-2"></i> Role Allocation
                    </button>
                @endcan
            </div>
        </div>

        {{-- Role Permissions: pick a role, then set that role's permissions. --}}
        <section data-tab-panel="permissions" role="tabpanel" class="user-access-panel">
            <div class="px-6 py-5 border-b border-zinc-800 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
                        <i class="fas fa-user-shield text-emerald-400"></i>
                        Role Permissions
                    </h1>
                    @if($selectedRole)
                        <p class="text-sm text-zinc-500">
                            {{ $selectedRole->users()->count() }}
                            {{ Str::plural('account', $selectedRole->users()->count()) }} on
                            {{ $selectedRole->label }}
                        </p>
                    @endif
                </div>

                @if($editableRoles->isEmpty())
                    <p class="text-sm text-zinc-500">No roles can be edited yet.</p>
                @else
                    <form method="GET" action="{{ route('user-access.index') }}#permissions" data-permission-role-form
                          class="flex flex-wrap items-center gap-3">
                        <select name="role" aria-label="Role to configure"
                                class="min-w-48 bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            @foreach($editableRoles as $role)
                                <option value="{{ $role->id }}" @selected($selectedRole && $selectedRole->is($role))>
                                    {{ $role->label }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-5 py-3 rounded-2xl font-medium text-zinc-300 whitespace-nowrap">
                            <i class="fas fa-eye mr-2"></i> Show permissions
                        </button>

                        <p class="text-xs text-zinc-500">
                            {{ $roles->where('has_full_access', true)->count() }}
                            {{ Str::plural('role', $roles->where('has_full_access', true)->count()) }}
                            {{ $roles->where('has_full_access', true)->count() === 1 ? 'keeps' : 'keep' }}
                            every permission automatically and {{ $roles->where('has_full_access', true)->count() === 1 ? 'is' : 'are' }} left out here.
                        </p>
                    </form>
                @endif
            </div>

            @if($selectedRole)
                <form action="{{ route('user-access.permissions.update', $selectedRole) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PATCH')

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-white">{{ $selectedRole->label }}</h2>
                            <p class="text-sm text-zinc-500">
                                Tick the activities this role can perform.
                                @if($selectedRole->grants_admin_access)
                                    Accounts on this role can sign in to the admin area.
                                @else
                                    Accounts on this role cannot sign in to the admin area, whatever is ticked here.
                                @endif
                            </p>
                        </div>

                        @can('managePermissions', \App\Models\Role::class)
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium whitespace-nowrap">
                                Save {{ $selectedRole->label }} Permissions
                            </button>
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="border border-zinc-800 rounded-2xl overflow-hidden">
                                <div class="bg-zinc-950 px-5 py-4 flex items-center justify-between gap-3">
                                    <h3 class="text-lg font-semibold text-zinc-100">{{ $group }}</h3>
                                    <span class="text-xs text-zinc-500">
                                        {{ $groupPermissions->filter(fn ($permission) => $selectedRole->permissions->contains('id', $permission->id))->count() }}
                                        / {{ $groupPermissions->count() }}
                                    </span>
                                </div>
                                <div class="divide-y divide-zinc-800">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex items-center gap-3 px-5 py-3 text-sm text-zinc-300 hover:bg-zinc-800/70">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                                                   {{ $selectedRole->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                                   {{ \Illuminate\Support\Facades\Gate::denies('managePermissions', \App\Models\Role::class) ? 'disabled' : '' }}>
                                            <span>{{ $permission->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            @else
                <p class="px-6 py-12 text-center text-zinc-500">No editable roles found.</p>
            @endif
        </section>

        {{-- Roles: create a role and decide what it may reach. --}}
        @can('manageRoles', \App\Models\Role::class)
            <section data-tab-panel="roles" role="tabpanel" class="user-access-panel hidden">
                <div class="px-6 py-5 border-b border-zinc-800 space-y-3">
                    <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                        <i class="fas fa-user-tag text-emerald-400"></i>
                        Roles
                    </h2>
                    <p class="text-sm text-zinc-500">
                        Create a role here, choose whether it can reach the admin area, then tick its permissions on the
                        Role Permissions tab. New roles appear everywhere automatically — no code changes needed.
                    </p>
                </div>

                <form action="{{ route('user-access.roles.store') }}" method="POST" class="px-6 py-6 space-y-5 border-b border-zinc-800">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div>
                            <label for="new-role-label" class="block text-sm text-zinc-400 mb-2">Label <span class="text-red-500">*</span></label>
                            <input id="new-role-label" type="text" name="label" value="{{ old('label') }}" required
                                   placeholder="Content Editor"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            @error('label') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="new-role-name" class="block text-sm text-zinc-400 mb-2">Key</label>
                            <input id="new-role-name" type="text" name="name" value="{{ old('name') }}"
                                   placeholder="content_editor"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <p class="mt-1 text-xs text-zinc-500">Optional. Generated from the label when left empty.</p>
                            @error('name') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="new-role-sort" class="block text-sm text-zinc-400 mb-2">Sort order</label>
                            <input id="new-role-sort" type="number" name="sort_order" value="{{ old('sort_order', 100) }}" min="0" max="9999"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            @error('sort_order') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center gap-3 text-sm text-zinc-300">
                                <input type="checkbox" name="grants_admin_access" value="1" @checked(old('grants_admin_access'))
                                       class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                Can sign in to the admin area
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="new-role-description" class="block text-sm text-zinc-400 mb-2">Description</label>
                        <input id="new-role-description" type="text" name="description" value="{{ old('description') }}"
                               placeholder="What this role is for"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        @error('description') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium">
                        <i class="fas fa-circle-plus mr-2"></i> Create role
                    </button>
                </form>

                <div class="divide-y divide-zinc-800">
                    @foreach($roles as $role)
                        <form action="{{ route('user-access.roles.settings.update', $role) }}" method="POST" class="p-6 space-y-5">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-lg font-semibold text-white">{{ $role->label }}</h3>
                                    <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs uppercase text-zinc-400 font-mono">{{ $role->name }}</span>

                                    @if($role->has_full_access)
                                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs uppercase text-emerald-300">Full access</span>
                                    @endif
                                    @if($role->grants_admin_access)
                                        <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs uppercase text-zinc-300">Admin area</span>
                                    @else
                                        <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs uppercase text-zinc-500">No admin area</span>
                                    @endif
                                    @if($role->isSystem())
                                        <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs uppercase text-zinc-500">Built in</span>
                                    @endif

                                    <span class="text-xs text-zinc-500">
                                        {{ $role->users()->count() }} {{ Str::plural('account', $role->users()->count()) }} ·
                                        {{ $role->permissions->count() }} {{ Str::plural('permission', $role->permissions->count()) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium">
                                        Save
                                    </button>
                                    @unless($role->isSystem())
                                        <button type="button"
                                                onclick="showDeleteModal('{{ route('user-access.roles.destroy', $role) }}', 'Delete the {{ $role->label }} role?')"
                                                class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-5 py-3 rounded-2xl font-medium text-red-400 hover:text-red-500">
                                            Delete
                                        </button>
                                    @endunless
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm text-zinc-400 mb-2">Label</label>
                                    <input type="text" name="label" value="{{ $role->label }}" required
                                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                </div>

                                <div>
                                    <label class="block text-sm text-zinc-400 mb-2">Key</label>
                                    <input type="text" name="name" value="{{ $role->name }}" {{ $role->isSystem() ? 'disabled' : '' }}
                                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                    @if($role->isSystem())
                                        <p class="mt-1 text-xs text-zinc-500">Built in roles keep their key.</p>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm text-zinc-400 mb-2">Sort order</label>
                                    <input type="number" name="sort_order" value="{{ $role->sort_order }}" min="0" max="9999"
                                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 text-sm text-zinc-300">
                                        <input type="checkbox" name="grants_admin_access" value="1" @checked($role->grants_admin_access)
                                               class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                        Admin area access
                                    </label>

                                    <label class="flex items-center gap-3 text-sm text-zinc-300">
                                        <input type="checkbox" name="has_full_access" value="1" @checked($role->has_full_access)
                                               {{ $role->isSystem() && $role->has_full_access ? 'disabled' : '' }}
                                               class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500">
                                        Full access (ignores all permissions)
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Description</label>
                                <input type="text" name="description" value="{{ $role->description }}"
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            </div>
                        </form>
                    @endforeach
                </div>
            </section>
        @endcan

        @can('createAdmin', \App\Models\Role::class)
            <section data-tab-panel="create-admin" role="tabpanel" class="user-access-panel hidden p-6">
                <div class="max-w-2xl">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                            <i class="fas fa-user-plus text-emerald-400"></i>
                            Create Admin
                        </h2>
                        <p class="text-sm text-zinc-500 mt-2">
                            Only roles that can sign in to the admin area are listed. Give the account a role, then adjust
                            its permissions on the Role Permissions tab.
                        </p>
                    </div>

                    <form action="{{ route('user-access.admins.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Username <span class="text-red-500">*</span></label>
                                <input type="text" name="username" value="{{ old('username') }}" required
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                @error('username') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                @error('name') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                @error('email') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Role <span class="text-red-500">*</span></label>
                                <select name="role_id"
                                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                    @foreach($staffRoles as $role)
                                        <option value="{{ $role->id }}" @selected((int) old('role_id', \App\Models\Role::idFor(\App\Models\Role::ADMIN)) === (int) $role->id)>
                                            {{ $role->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                @error('password') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm text-zinc-400 mb-2">Confirm <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" required
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                            Create Admin
                        </button>
                    </form>
                </div>
            </section>
        @endcan

        <section data-tab-panel="admins" role="tabpanel" class="user-access-panel hidden">
            <div class="px-6 py-5 border-b border-zinc-800">
                <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                    <i class="fas fa-users-gear text-emerald-400"></i>
                    Admin Accounts
                </h2>
                <p class="text-sm text-zinc-500 mt-2">Every account on a role with admin area access.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full">
                    <thead class="bg-zinc-950">
                        <tr class="border-b border-zinc-800">
                            <th class="px-6 py-4 text-left">Name</th>
                            <th class="px-6 py-4 text-left">Username</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @forelse($admins as $admin)
                            <tr class="hover:bg-zinc-800/70">
                                <td class="px-6 py-4 font-medium text-white">{{ $admin->name }}</td>
                                <td class="px-6 py-4">{{ $admin->username }}</td>
                                <td class="px-6 py-4">{{ $admin->email ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $admin->roleLabel() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-zinc-500">No admins found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @can('assignRole', \App\Models\Role::class)
            <section data-tab-panel="allocation" role="tabpanel" class="user-access-panel hidden">
                <div class="px-6 py-5 border-b border-zinc-800 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                            <i class="fas fa-key text-emerald-400"></i>
                            Role Allocation
                        </h2>
                        <p class="text-sm text-zinc-500">
                            {{ number_format($users->total()) }} {{ Str::plural('user', $users->total()) }} found
                        </p>
                    </div>

                    <form method="GET" action="{{ route('user-access.index') }}#allocation" data-allocation-filter
                          class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 min-w-48">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none"></i>
                            <input type="search" name="search" value="{{ $search }}" style="padding-left: 2.75rem"
                                   placeholder="Search name or email"
                                   aria-label="Search user by name, username or email"
                                   title="Match by name or username, or by the full email address"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">
                        </div>

                        <select name="role_filter" aria-label="Filter by role"
                                class="min-w-48 bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Filter role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $roleFilter === $role->name ? 'selected' : '' }}>
                                    {{ $role->label }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium whitespace-nowrap">
                            <i class="fas fa-filter mr-2"></i> Apply
                        </button>

                        @if($search !== '' || $roleFilter !== '')
                            <a href="{{ route('user-access.index') }}#allocation"
                               class="bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 px-5 py-3 rounded-2xl font-medium text-zinc-300 whitespace-nowrap">
                                Clear
                            </a>
                        @endif
                    </form>

                    <p class="text-xs text-zinc-500">
                        Name and username match partially; emails are stored encrypted, so search them by the full address.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[980px] w-full">
                        <thead class="bg-zinc-950">
                            <tr class="border-b border-zinc-800">
                                <th class="px-6 py-4 text-left">Name</th>
                                <th class="px-6 py-4 text-left">Username</th>
                                <th class="px-6 py-4 text-left">Email</th>
                                <th class="px-6 py-4 text-left">Current Role</th>
                                <th class="px-6 py-4 text-left">Allocate Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @forelse($users as $user)
                                <tr class="hover:bg-zinc-800/70">
                                    <td class="px-6 py-4 font-medium text-white">{{ $user->name }}</td>
                                    <td class="px-6 py-4">{{ $user->username }}</td>
                                    <td class="px-6 py-4">{{ $user->email ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $user->roleLabel() }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('user-access.roles.update', $user) }}" method="POST" class="flex gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role_id"
                                                    class="min-w-48 bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ (int) $user->role_id === (int) $role->id ? 'selected' : '' }}>
                                                        {{ $role->label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium">
                                                Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                        @if($search !== '' || $roleFilter !== '')
                                            No users match your search or role filter.
                                        @else
                                            No users found.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-zinc-800">
                    {{ $users->withQueryString()->links() }}
                </div>
            </section>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-user-access-tabs]');
    if (!root) return;

    const tabs = Array.from(root.querySelectorAll('[data-tab-target]'));
    const panels = Array.from(root.querySelectorAll('[data-tab-panel]'));
    function showTab(name, updateHash = true) {
        const selected = tabs.find(tab => tab.dataset.tabTarget === name) || tabs[0];
        if (!selected) return;

        tabs.forEach(tab => {
            const active = tab === selected;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.classList.toggle('bg-emerald-600', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('text-zinc-400', !active);
        });

        panels.forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.tabPanel !== selected.dataset.tabTarget);
        });

        if (updateHash) {
            history.replaceState(null, '', `#${selected.dataset.tabTarget}`);
        }
    }

    tabs.forEach(tab => tab.addEventListener('click', () => showTab(tab.dataset.tabTarget)));
    showTab(window.location.hash.replace('#', '') || tabs[0]?.dataset.tabTarget, false);

    // Role Permissions: swapping the role reloads that role's permissions.
    const roleForm = root.querySelector('[data-permission-role-form]');
    roleForm?.querySelector('select[name="role"]')?.addEventListener('change', () => roleForm.requestSubmit());

    // Role Allocation: keep the tab anchored and apply the role filter immediately.
    const allocationForm = root.querySelector('[data-allocation-filter]');
    if (!allocationForm) return;

    const anchor = (url) => (url && ! url.includes('#') ? `${url}#allocation` : url);

    allocationForm.addEventListener('submit', () => {
        allocationForm.setAttribute('action', anchor(allocationForm.getAttribute('action') || ''));
    });

    allocationForm.querySelector('select[name="role_filter"]')?.addEventListener('change', () => {
        allocationForm.requestSubmit();
    });

    // Pagination (and any other) links inside the panel keep the allocation tab active.
    root.querySelectorAll('[data-tab-panel="allocation"] a[href]').forEach(link => {
        const href = link.getAttribute('href');
        if (href && ! href.startsWith('#') && ! href.startsWith('javascript:')) {
            link.setAttribute('href', anchor(href));
        }
    });
});
</script>
@endpush
