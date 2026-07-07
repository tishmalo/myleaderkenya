<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tuko Kadi') }} - Aspirant</title>
    <link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100">
<div class="min-h-screen">
    <header class="bg-zinc-900 border-b border-zinc-800 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('aspirant.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/myleader.png') }}" class="w-10 h-10 rounded-2xl object-cover" alt="ML Kenya">
                <span class="font-semibold text-xl">Aspirant Portal</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-zinc-400 hover:text-red-400 flex items-center gap-2"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </header>
    <main class="p-6 md:p-8">@yield('content')</main>
</div>
</body>
</html>
