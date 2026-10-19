@extends('layouts.app')

@section('page_title', 'Create Message')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-white">Create Message</h1>
            <p class="mt-2 text-sm text-zinc-400">Posts a message into a constituency chat, where voters can see and react to it.</p>
        </div>
        <a href="{{ route('dashboard.messages') }}" class="text-zinc-400 hover:text-white">Back to Messages</a>
    </div>

    <form action="{{ route('messages.store') }}" method="POST">
        @csrf

        <div class="space-y-6 rounded-3xl border border-zinc-800 bg-zinc-900 p-6 md:p-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="county" class="mb-2 block text-sm text-zinc-400">County <span class="text-red-500">*</span></label>
                    <select id="county" name="county" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white focus:border-emerald-500 focus:outline-none">
                        <option value="">Select a county</option>
                        @foreach($counties as $countyName)
                            <option value="{{ $countyName }}" @selected(old('county') === $countyName)>{{ $countyName }}</option>
                        @endforeach
                    </select>
                    @error('county')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="constituency" class="mb-2 block text-sm text-zinc-400">Constituency <span class="text-red-500">*</span></label>
                    <input id="constituency" name="constituency" list="constituency-options" value="{{ old('constituency') }}" required
                           autocomplete="off"
                           placeholder="Select a county first"
                           class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white placeholder:text-zinc-500 focus:border-emerald-500 focus:outline-none">
                    <datalist id="constituency-options"></datalist>
                    <p id="constituency-hint" class="mt-1 text-xs text-zinc-500">Choose a county to see its constituencies.</p>
                    @error('constituency')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="message" class="mb-2 block text-sm text-zinc-400">Message <span class="text-red-500">*</span></label>
                <textarea id="message" name="message" rows="6" maxlength="500" required
                          placeholder="What do you want to tell voters in this constituency?"
                          class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white placeholder:text-zinc-500 focus:border-emerald-500 focus:outline-none">{{ old('message') }}</textarea>
                <div class="mt-2 flex items-center justify-between text-xs text-zinc-500">
                    <span>Keep it short and clear.</span>
                    <span><span id="message-count">0</span>/500</span>
                </div>
                @error('message')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-3 font-semibold text-white transition-colors hover:bg-emerald-700">
                <i class="fas fa-paper-plane mr-2"></i> Send Message
            </button>
            <a href="{{ route('dashboard.messages') }}" class="rounded-2xl border border-zinc-700 px-6 py-3 font-medium text-zinc-300 transition-colors hover:text-white">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var constituencies = @json(config('kenya.constituencies') ?? []);
        var county = document.getElementById('county');
        var constituency = document.getElementById('constituency');
        var options = document.getElementById('constituency-options');
        var hint = document.getElementById('constituency-hint');
        var message = document.getElementById('message');
        var counter = document.getElementById('message-count');

        function syncConstituencies() {
            var list = constituencies[county.value] || [];

            options.innerHTML = '';
            list.forEach(function (name) {
                var option = document.createElement('option');
                option.value = name;
                options.appendChild(option);
            });

            constituency.placeholder = list.length ? 'Start typing to search' : 'Enter the constituency name';
            hint.textContent = list.length
                ? list.length + ' constituencies in ' + county.value + '.'
                : (county.value ? 'No constituency list for this county - type the name.' : 'Choose a county to see its constituencies.');
        }

        function syncCounter() {
            counter.textContent = message.value.length;
        }

        if (county) {
            county.addEventListener('change', function () {
                constituency.value = '';
                syncConstituencies();
                constituency.focus();
            });

            syncConstituencies();
        }

        if (message) {
            message.addEventListener('input', syncCounter);
            syncCounter();
        }
    })();
</script>
@endpush
