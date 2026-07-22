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

        <section data-tab-panel="permissions" role="tabpanel" class="user-access-panel">
            <div class="px-6 py-5 border-b border-zinc-800">
                <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
                    <i class="fas fa-user-shield text-emerald-400"></i>
                    Role Permissions
                </h1>
            </div>

            <div class="divide-y divide-zinc-800">
                @foreach($permissionRoles as $role)
                    <form action="{{ route('user-access.permissions.update', $role) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-white">{{ $role->label }}</h2>
                                <p class="text-sm text-zinc-500">Tick the activities this role can perform.</p>
                            </div>

                            @can('managePermissions', \App\Models\Role::class)
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-5 py-3 rounded-2xl font-medium">
                                        Save {{ $role->label }} Permissions
                                    </button>
                            @endcan
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="border border-zinc-800 rounded-2xl overflow-hidden">
                                    <div class="bg-zinc-950 px-5 py-4">
                                        <h3 class="text-lg font-semibold text-zinc-100">{{ $group }}</h3>
                                    </div>
                                    <div class="divide-y divide-zinc-800">
                                        @foreach($groupPermissions as $permission)
                                            <label class="flex items-center gap-3 px-5 py-3 text-sm text-zinc-300 hover:bg-zinc-800/70">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permission->id }}"
                                                       class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                                                       {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                                       {{ \Illuminate\Support\Facades\Gate::denies('managePermissions', \App\Models\Role::class) ? 'disabled' : '' }}>
                                                <span>{{ $permission->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                @endforeach
            </div>
        </section>

        @can('createAdmin', \App\Models\Role::class)
            <section data-tab-panel="create-admin" role="tabpanel" class="user-access-panel hidden p-6">
                <div class="max-w-2xl">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                            <i class="fas fa-user-plus text-emerald-400"></i>
                            Create Admin
                        </h2>
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

                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            @error('email') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
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
                <div class="px-6 py-5 border-b border-zinc-800">
                    <h2 class="text-2xl font-semibold text-white flex items-center gap-3">
                        <i class="fas fa-key text-emerald-400"></i>
                        Role Allocation
                    </h2>
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
                                    <td colspan="5" class="px-6 py-12 text-center text-zinc-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-5 border-t border-zinc-800">
                    {{ $users->links() }}
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
});
</script>
@endpush
