@extends('layouts.app')

@section('page_title', 'Add New User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-user-plus text-emerald-500"></i>
            Add New User
        </h1>
        <a href="{{ route('users.index') }}" class="text-zinc-400 hover:text-white">
            ← Back to Users
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="johndoe">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email</label>
                    <input type="email" name="email"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="john@example.com">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="+254712345678">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Gender</label>
                    <select name="gender" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Year of Birth -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Year of Birth</label>
                    <input type="number" name="year_of_birth" min="1900" max="{{ date('Y') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="1995">
                </div>
            </div>

            <!-- Cascading Location Fields -->
            <div class="mt-8">
                <label class="block text-sm text-zinc-400 mb-4">Location</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- County -->
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">County <span class="text-red-500">*</span></label>
                        <select name="county" id="county" required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Select County</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->name }}">{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Constituency -->
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Constituency <span class="text-red-500">*</span></label>
                        <select name="constituency" id="constituency" required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Select Constituency</option>
                        </select>
                    </div>

                    <!-- Ward -->
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Ward <span class="text-red-500">*</span></label>
                        <select name="ward" id="ward" required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Select Ward</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="mt-10">
                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countySelect = document.getElementById('county');
    const constituencySelect = document.getElementById('constituency');
    const wardSelect = document.getElementById('ward');

    // Load constituencies when county changes
    countySelect.addEventListener('change', function() {
        const countyName = this.value;
        if (!countyName) {
            constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            return;
        }

        fetch(`/api/constituencies/by-county?county=${encodeURIComponent(countyName)}`)
            .then(response => response.json())
            .then(data => {
                constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
                data.forEach(constituency => {
                    const option = document.createElement('option');
                    option.value = constituency.name || constituency;
                    option.textContent = constituency.name || constituency;
                    constituencySelect.appendChild(option);
                });
                wardSelect.innerHTML = '<option value="">Select Ward</option>';
            })
            .catch(err => console.error('Error loading constituencies:', err));
    });

    // Load wards when constituency changes
    constituencySelect.addEventListener('change', function() {
        const constituencyName = this.value;
        if (!constituencyName) {
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            return;
        }

        fetch(`/api/wards/by-constituency?constituency=${encodeURIComponent(constituencyName)}`)
            .then(response => response.json())
            .then(data => {
                wardSelect.innerHTML = '<option value="">Select Ward</option>';
                data.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward.name || ward;
                    option.textContent = ward.name || ward;
                    wardSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error loading wards:', err));
    });
});
</script>
@endpush