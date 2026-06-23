@extends('layouts.landing')

@section('page_title', 'Welcome to MyLeader')

@section('content')
<div class="min-h-screen bg-zinc-950 text-white flex items-center justify-center p-6">
    <div class="max-w-5xl w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold tracking-tight mb-3">
                Welcome to <span class="text-emerald-500">MyLeader</span>
            </h1>
            <p class="text-zinc-400 text-xl max-w-md mx-auto">
                Join the community. Connect with your county, constituency, and make your voice heard.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            
            <!-- Login Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-2xl">
                <div class="flex items-center gap-3 mb-8">
                    <i class="fas fa-sign-in-alt text-emerald-500 text-2xl"></i>
                    <h2 class="text-3xl font-semibold">Login</h2>
                </div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Username -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500 transition"
                                   placeholder="johndoe">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-emerald-500 transition"
                                   placeholder="••••••••">
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="w-4 h-4 accent-emerald-500">
                                <span class="text-zinc-400">Remember me</span>
                            </label>
                            <a href="#" class="text-emerald-500 hover:text-emerald-400">Forgot password?</a>
                        </div>

                        <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg transition">
                            Login
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-zinc-400 text-sm">
                    Don't have an account? 
                    <button onclick="showRegister()" 
                            class="text-emerald-500 hover:text-emerald-400 font-medium">Register here</button>
                </div>
            </div>

            <!-- Register Card -->
            <div id="registerCard" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-2xl hidden md:block">
                <div class="flex items-center gap-3 mb-8">
                    <i class="fas fa-user-plus text-emerald-500 text-2xl"></i>
                    <h2 class="text-3xl font-semibold">Create Account</h2>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Username -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Email</label>
                            <input type="email" name="email"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                            <input type="tel" name="phone"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Gender</label>
                            <select name="gender" 
                                    class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Year of Birth -->
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Year of Birth</label>
                            <input type="number" name="year_of_birth" min="1900" max="{{ date('Y') }}"
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
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
                                class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg transition">
                            Create My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mobile Register Toggle -->
        <div class="md:hidden mt-8 text-center">
            <button onclick="toggleRegister()" 
                    id="mobileToggle"
                    class="text-emerald-500 hover:text-emerald-400 font-medium">
                Don't have an account? Register →
            </button>
        </div>
    </div>
</div>

<script>
function showRegister() {
    document.getElementById('registerCard').classList.remove('hidden');
}

function toggleRegister() {
    const card = document.getElementById('registerCard');
    card.classList.toggle('hidden');
}
</script>
@endsection