@extends('layouts.app')

@section('page_title', 'User Access')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
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

    <div class="grid grid-cols-1 xl:grid-cols-[420px_minmax(0,1fr)] gap-8">
        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-white flex items-center gap-3">
                    <i class="fas fa-user-shield text-emerald-400"></i>
                    Create Admin
                </h1>
            </div>

            <form action="{{ route('user-access.admins.store') }}" method="POST" class="space-y-5">
                @csrf

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

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    @error('email') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    @error('phone') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        @error('password') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Create Admin
                </button>
            </form>
        </section>

        <section class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
            <div class="px-6 py-5 border-b border-zinc-800">
                <h2 class="text-xl font-semibold text-white flex items-center gap-3">
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
    </div>

    <section class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="px-6 py-5 border-b border-zinc-800">
            <h2 class="text-xl font-semibold text-white flex items-center gap-3">
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
</div>
@endsection
