@extends('layouts.app')

@section('page_title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-user-edit text-emerald-500"></i>
            Edit User: {{ $user->username }}
        </h1>
        <a href="{{ route('users.index') }}" class="text-zinc-400 hover:text-white flex items-center gap-2">
            ← Back to Users
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username (disabled) -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Username</label>
                    <input type="text" value="{{ $user->username }}" disabled
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-zinc-500">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ $user->name }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ $user->phone }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Gender</label>
                    <select name="gender" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Year of Birth -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Year of Birth</label>
                    <input type="number" name="year_of_birth" value="{{ $user->year_of_birth }}" min="1900" max="{{ date('Y') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Location Fields -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">County</label>
                    <input type="text" name="county" value="{{ $user->county }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                    <input type="text" name="constituency" value="{{ $user->constituency }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Ward</label>
                    <input type="text" name="ward" value="{{ $user->ward }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="mt-10 flex gap-4">
                <a href="{{ route('users.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection