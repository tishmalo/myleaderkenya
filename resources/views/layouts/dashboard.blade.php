<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MLK') }} - Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        .sidebar-link { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { background-color: #18181b; transform: translateX(6px); }
        .sidebar-link.active {
            background-color: #27272a;
            border-left: 4px solid rgb(16 185 129);
            color: white;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.2);
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 overflow-x-hidden">

<div class="flex h-screen w-screen max-w-full overflow-hidden" x-data="{ currentTab: 1 }">

    <!-- Sidebar -->
    <div class="w-72 bg-zinc-900 border-r border-zinc-800 flex flex-col">
        <div class="p-6 border-b border-zinc-800 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold">V</div>
            <div>
                <h1 class="text-3xl font-semibold tracking-tighter">VoteHub</h1>
                <p class="text-xs text-emerald-400 -mt-1">ADMIN PANEL</p>
            </div>
        </div>

        <nav class="flex-1 p-6 overflow-y-auto">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300"
                       :class="{ 'active': currentTab === 1 }" @click="currentTab = 1">
                        <i class="fas fa-chart-line w-5"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300"
                       :class="{ 'active': currentTab === 2 }" @click="currentTab = 2">
                        <i class="fas fa-comment-dots w-5"></i>
                        <span>Messages</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300"
                       :class="{ 'active': currentTab === 3 }" @click="currentTab = 3">
                        <i class="fas fa-chart-pie w-5"></i>
                        <span>Voter Stats</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300"
                       :class="{ 'active': currentTab === 4 }" @click="currentTab = 4">
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span>Polling Stations</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('users.index') }}" 
                       class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                        <i class="fas fa-user-check w-5"></i>
                        <span>Registered Voters</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('locations.index') }}" 
                       class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                        <i class="fas fa-map w-5"></i>
                        <span>Voter Locations</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-6 border-t border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-zinc-500">Administrator</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="min-w-0 flex-1 flex flex-col overflow-hidden">
        <header class="bg-zinc-900 border-b border-zinc-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-semibold text-white" x-text="getPageTitle()"></h2>
            </div>
            <div class="flex items-center gap-6">
                <!-- Search, notifications, logout here (same as previous) -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-zinc-400 hover:text-red-400">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden p-8 bg-zinc-950">
            <!-- All tab contents live here -->
            <div id="tab-1" class="tab-content" :class="{ 'active': currentTab === 1 }">
                @yield('overview-content')   {{-- Put your dashboard overview here --}}
            </div>

            <div id="tab-2" class="tab-content" :class="{ 'active': currentTab === 2 }">
                @include('messages.index')
            </div>

            <div id="tab-3" class="tab-content" :class="{ 'active': currentTab === 3 }">
                @include('voters.stats')
            </div>

            <div id="tab-4" class="tab-content" :class="{ 'active': currentTab === 4 }">
                @include('stations.index')   {{-- or wherever your stations blade is --}}
            </div>

            <!-- Other pages like users and locations remain as separate routes -->
        </main>
    </div>
</div>

<script>
    function getPageTitle() {
        const titles = {
            1: 'Dashboard Overview',
            2: 'Live Messages',
            3: 'Voter Statistics',
            4: 'Polling Stations'
        };
        return titles[currentTab] || 'Dashboard';
    }
</script>

@stack('scripts')
</body>
</html>
