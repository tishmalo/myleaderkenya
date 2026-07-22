<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tuko Kadi') }} - Admin Panel</title>

    <link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/mlkfav.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        .sidebar-link { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { background-color: #18181b; transform: translateX(6px); }
        .sidebar-link.active { background-color: #27272a; border-left: 4px solid rgb(16 185 129); color: white; }
        .sidebar-dropdown-summary { list-style: none; transition: background-color 0.2s ease, color 0.2s ease; }
        .sidebar-dropdown-summary::-webkit-details-marker { display: none; }
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.2); }
        .button-loading-spinner { display: inline-block; width: 1rem; height: 1rem; border: 2px solid currentColor; border-right-color: transparent; border-radius: 9999px; animation: button-spin 0.65s linear infinite; }
        [data-loading="true"] { cursor: wait; opacity: 0.82; }
        @keyframes button-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 overflow-x-hidden">

<div class="flex h-screen w-screen max-w-full overflow-hidden">
    <div id="adminSidebarBackdrop" class="fixed inset-0 z-40 hidden bg-black/70 md:hidden" data-sidebar-close></div>

    @include('components.admin-sidebar')

    <div class="min-w-0 flex-1 flex flex-col overflow-hidden">
        <header class="bg-zinc-900 border-b border-zinc-800 px-4 py-4 md:px-8 md:py-5 flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3 md:gap-4">
                <button type="button" class="md:hidden inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-zinc-800 text-zinc-300 hover:bg-zinc-800" data-sidebar-open aria-label="Open admin menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="truncate text-xl font-semibold text-white md:text-2xl">@yield('page_title', 'Dashboard Overview')</h2>
            </div>
            <div class="flex shrink-0 items-center gap-3 md:gap-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-zinc-400 hover:text-red-400 transition-colors" data-loading-label="Logging out...">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-zinc-950 p-4 md:p-8">
            @yield('content')
        </main>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[9999] px-4">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-md p-6 text-center md:p-8">
        <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-6 md:text-6xl"></i>
        <h3 class="text-2xl font-semibold mb-3">Are you sure?</h3>
        <p id="deleteMessage" class="text-zinc-400 mb-8">This action cannot be undone.</p>

        <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
            <button type="button" onclick="hideDeleteModal()" class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">Cancel</button>
            <button type="button" onclick="confirmDelete(this)" class="flex-1 bg-red-600 hover:bg-red-700 py-4 rounded-2xl font-medium" data-loading-label="Deleting...">Yes, Delete</button>
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
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentDeleteUrl = '';
}

function setButtonLoading(button, label = 'Processing...') {
    if (!button || button.dataset.loading === 'true') return;
    button.dataset.loading = 'true';
    button.dataset.originalHtml = button.innerHTML;
    button.style.minWidth = `${button.offsetWidth}px`;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = `<span class="inline-flex items-center justify-center gap-2"><span class="button-loading-spinner" aria-hidden="true"></span><span>${label}</span></span>`;
}

function confirmDelete(button = null) {
    if (!currentDeleteUrl) return;
    setButtonLoading(button, button?.dataset.loadingLabel || 'Deleting...');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = currentDeleteUrl;
    form.innerHTML = `@csrf @method('DELETE')`;
    document.body.appendChild(form);
    form.submit();
}

window.showDeleteModal = showDeleteModal;
window.hideDeleteModal = hideDeleteModal;
window.confirmDelete = confirmDelete;
window.setButtonLoading = setButtonLoading;

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const openButton = document.querySelector('[data-sidebar-open]');
    const closeTargets = document.querySelectorAll('[data-sidebar-close]');

    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
    }

    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
    }

    openButton?.addEventListener('click', openSidebar);
    closeTargets.forEach((target) => target.addEventListener('click', closeSidebar));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeSidebar(); });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.noLoader === 'true') return;

        const submitter = event.submitter || document.activeElement;
        const button = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
            ? submitter
            : form.querySelector('button[type="submit"], input[type="submit"]');

        if (button instanceof HTMLButtonElement) {
            setButtonLoading(button, button.dataset.loadingLabel || 'Processing...');
        } else if (button instanceof HTMLInputElement) {
            button.dataset.originalValue = button.value;
            button.value = button.dataset.loadingLabel || 'Processing...';
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }
    }, true);
});
</script>

@stack('scripts')
</body>
</html>
