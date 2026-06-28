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
        .sidebar-dropdown-summary {
            list-style: none;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .sidebar-dropdown-summary::-webkit-details-marker {
            display: none;
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

    @include('components.admin-sidebar')

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


