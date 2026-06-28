<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tuko Kadi') }} - Admin Panel</title>

     <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    
    <!-- Optional: Apple Touch Icon -->
    <link rel="apple-touch-icon" href="{{ asset('images/mlkfav.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        .sidebar-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link:hover {
            background-color: #18181b;
            transform: translateX(6px);
        }
        .sidebar-link.active {
            background-color: #27272a;
            border-left: 4px solid rgb(16 185 129);
            color: white;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.2);
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="w-72 bg-zinc-900 border-r border-zinc-800 flex flex-col">
        <div class="p-6 border-b border-zinc-800 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl overflow-hidden flex-shrink-0">
    <img 
        src="{{ asset('images/myleader.png') }}" 
        alt="Tuko Kadi Logo"
        class="w-full h-full object-cover"
    >
</div>
            <div>
                <h1 class="text-3xl font-semibold tracking-tighter">ML KENYA</h1>
                <p class="text-xs text-emerald-400 -mt-1">THE KENYA. WE WANT</p>
            </div>
        </div>

        <nav class="flex-1 p-6 overflow-y-auto">
    <ul class="space-y-2">
        <li>
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-chart-line w-5"></i>
                <span>Overview</span>
            </a>
        </li>
        <li class="mt-8">
            <p class="px-5 text-xs font-medium text-zinc-500 uppercase tracking-widest mb-3">Voter</p>
        </li>
        <li>
            <a href="{{ route('users.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-user-check w-5"></i>
                <span>Voters</span>
            </a>
        </li>

        <li>
            <a href="{{ route('dashboard.messages') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-comment-dots w-5"></i>
                <span>Voter Messages</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dashboard.stats') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-chart-pie w-5"></i>
                <span>Voter Stats</span>
            </a>
        </li>
        <li>
            <a href="{{ route('locations.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-map w-5"></i>
                <span>Voter Locations</span>
            </a>
        </li>
        <li>
    <a href="{{ route('positions.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
        <i class="fas fa-user-tie w-5"></i>
        <span>Positions</span>
    </a>
</li>
<li>
    <a href="{{ route('candidates.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
        <i class="fas fa-user-tie w-5"></i>
        <span>Candidates</span>
    </a>
</li>
<li>
    <a href="{{ route('categories.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
        <i class="fas fa-user-tie w-5"></i>
        <span>News Categories</span>
    </a>
</li>
<li>
    <a href="{{ route('news.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
        <i class="fas fa-user-tie w-5"></i>
        <span>News</span>
    </a>
</li><li>
    <a href="{{ route('campaign-tools.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
        <i class="fas fa-bullhorn w-5"></i>
        <span>Campaign Tools</span>
    </a>
</li>
        <li>
            <a href="{{ route('tags.index') }}" 
                class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-tags w-5"></i>
                <span>Tags & Topics</span>
            </a>
        </li>
        <li>
            <a href="{{ route('payment-methods.index') }}" 
            class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
            <i class="fas fa-money-bill-wave w-5"></i>
            <span>Donor Settings</span>
            </a>
        </li>

        <!-- New Location Hierarchy Section -->
        <li class="mt-8">
            <p class="px-5 text-xs font-medium text-zinc-500 uppercase tracking-widest mb-3">Data</p>
        </li>
        <li>
            <a href="{{ route('blocs.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-users w-5"></i>
                <span>Blocs</span>
            </a>
        </li>
        <li>
            <a href="{{ route('counties.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-map w-5"></i>
                <span>Counties</span>
            </a>
        </li>
        <li>
            <a href="{{ route('constituencies.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-map-marker-alt w-5"></i>
                <span>Constituencies</span>
            </a>
        </li>
        <li>
            <a href="{{ route('wards.index') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-layer-group w-5"></i>
                <span>Wards</span>
            </a>
        </li>

        <!-- Existing Links -->
        <li class="mt-8">
            <a href="{{ route('dashboard.stations') }}" class="sidebar-link flex items-center gap-3 px-5 py-4 rounded-2xl text-zinc-300">
                <i class="fas fa-map-marker-alt w-5"></i>
                <span>Polling Stations</span>
            </a>
        </li>
    </ul>
</nav>

        <div class="p-6 border-t border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-zinc-500">Administrator</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-zinc-900 border-b border-zinc-800 px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-semibold text-white">@yield('page_title', 'Dashboard Overview')</h2>
            </div>
            <div class="flex items-center gap-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-zinc-400 hover:text-red-400 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-8 bg-zinc-950">
            @yield('content')
        </main>
    </div>
</div>

<!-- Reusable Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-[9999]">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-md mx-4 p-8 text-center">
        <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-6"></i>
        <h3 class="text-2xl font-semibold mb-2">Are you sure?</h3>
        <p class="text-zinc-400 mb-8" id="deleteMessage">This action cannot be undone.</p>
        
        <div class="flex gap-4">
            <button onclick="hideDeleteModal()" 
                    class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium">Cancel</button>
            <button onclick="confirmDelete()" 
                    class="flex-1 bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-medium">Yes, Delete</button>
        </div>
    </div>
</div>

@push('scripts')
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[9999]">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-md mx-4 p-8 text-center">
        <i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-6"></i>
        <h3 class="text-2xl font-semibold mb-3">Are you sure?</h3>
        <p id="deleteMessage" class="text-zinc-400 mb-8">This action cannot be undone.</p>
        
        <div class="flex gap-4">
            <button onclick="hideDeleteModal()" 
                    class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">
                Cancel
            </button>
            <button onclick="confirmDelete()" 
                    class="flex-1 bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-medium">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script>
let currentDeleteUrl = '';

function showDeleteModal(url, message = 'This action cannot be undone.') {
    currentDeleteUrl = url;
    document.getElementById('deleteMessage').textContent = message;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
}

function confirmDelete() {
    if (currentDeleteUrl) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = currentDeleteUrl;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endpush
@stack('scripts')
</body>
</html>