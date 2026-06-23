<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - My Leader Kenya</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .flag-stripe {
            height: 5px;
            background: linear-gradient(90deg, #000 0% 33.3%, #BB0000 33.3% 66.6%, #006600 66.6% 100%);
        }
        .login-card {
            backdrop-filter: blur(16px);
            background: rgba(24, 24, 27, 0.95);
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="bg-zinc-950 min-h-screen flex items-center justify-center p-4">
    <div class="flag-stripe fixed top-0 left-0 right-0 z-50"></div>

    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-block">
                <div class="w-20 h-20 mx-auto mb-4 bg-zinc-900 rounded-2xl flex items-center justify-center border border-zinc-800">
                    <img src="{{ asset('images/myleader.png') }}" alt="My Leader Kenya" class="w-14 h-14 object-contain">
                </div>
            </a>
            <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
            <p class="text-zinc-400">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="login-card border border-zinc-800 rounded-3xl p-8 shadow-2xl">
            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300 mb-2">
                        <i class="fas fa-envelope mr-2 text-zinc-500"></i>Email Address
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="input-field w-full px-4 py-3 bg-zinc-900 border border-zinc-700 rounded-2xl text-white placeholder-zinc-500 focus:outline-none"
                           placeholder="Enter your email">
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300 mb-2">
                        <i class="fas fa-lock mr-2 text-zinc-500"></i>Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="input-field w-full px-4 py-3 bg-zinc-900 border border-zinc-700 rounded-2xl text-white placeholder-zinc-500 focus:outline-none pr-12"
                               placeholder="Enter your password">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300">
                            <i id="toggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-zinc-900">
                        <span class="ml-2 text-sm text-zinc-400">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login w-full py-3 px-6 rounded-2xl text-white font-semibold text-sm uppercase tracking-wider">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>
            </form>
        </div>

        <!-- Download App Button -->
        <div class="mt-6">
            <a href="{{ config('constants.app_download_link') }}" target="_blank" rel="noopener noreferrer"
               class="block w-full py-3 px-6 bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 hover:border-emerald-500/50 rounded-2xl text-center text-white font-semibold text-sm uppercase tracking-wider transition-all">
                <i class="fab fa-google-play mr-2 text-emerald-400"></i>Download Mobile App
                <i class="fas fa-external-link-alt ml-2 text-xs text-zinc-500"></i>
            </a>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-zinc-400 text-sm">
                Don't have an account?
                <a href="{{ route('landing') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold transition-colors">
                    Join Tuko Kadi
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="mt-4 text-center">
            <a href="{{ route('landing') }}" class="text-zinc-500 hover:text-zinc-300 text-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
