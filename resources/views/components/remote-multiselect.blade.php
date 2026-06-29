@props([
    'name',
    'label',
    'searchUrl',
    'selected' => [],
    'placeholder' => 'Search...',
    'emptyText' => 'No options found.',
    'minChars' => 2,
])

@php
    $selectedOptions = collect($selected)->map(function ($option) {
        if (is_array($option)) {
            return [
                'value' => (string) data_get($option, 'value', data_get($option, 'id')),
                'label' => data_get($option, 'label', data_get($option, 'text')),
            ];
        }

        return [
            'value' => (string) data_get($option, 'id'),
            'label' => data_get($option, 'name'),
        ];
    })->filter(fn ($option) => $option['value'] !== '' && filled($option['label']))->values();

    $componentId = 'remote-multiselect-' . md5($name . $label . uniqid('', true));
@endphp

<div class="space-y-2" data-remote-multiselect data-search-url="{{ $searchUrl }}" data-min-chars="{{ $minChars }}" data-empty-text="{{ $emptyText }}" id="{{ $componentId }}">
    <label class="block text-sm text-zinc-400">{{ $label }}</label>

    <div class="rounded-2xl border border-zinc-700 bg-zinc-800 overflow-hidden focus-within:border-emerald-500 transition-colors">
        <div class="p-3 border-b border-zinc-700 space-y-3">
            <input type="search"
                   data-remote-multiselect-input
                   placeholder="{{ $placeholder }}"
                   class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-4 py-2 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">

            <div class="flex flex-wrap gap-2" data-remote-multiselect-selected>
                @foreach($selectedOptions as $option)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-sm text-emerald-100" data-remote-multiselect-pill data-value="{{ $option['value'] }}">
                        {{ $option['label'] }}
                        <button type="button" class="text-emerald-200 hover:text-white" data-remote-multiselect-remove aria-label="Remove {{ $option['label'] }}">&times;</button>
                        <input type="hidden" name="{{ $name }}" value="{{ $option['value'] }}" data-remote-multiselect-hidden>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="max-h-64 overflow-y-auto p-2 space-y-1" data-remote-multiselect-results>
            <div class="px-3 py-6 text-center text-sm text-zinc-500" data-remote-multiselect-message>
                Type at least {{ $minChars }} characters to search.
            </div>
        </div>
    </div>

    <div class="text-xs text-zinc-500" data-remote-multiselect-summary></div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-remote-multiselect]').forEach(function (component) {
                    var input = component.querySelector('[data-remote-multiselect-input]');
                    var selectedWrap = component.querySelector('[data-remote-multiselect-selected]');
                    var resultsWrap = component.querySelector('[data-remote-multiselect-results]');
                    var message = component.querySelector('[data-remote-multiselect-message]');
                    var summary = component.querySelector('[data-remote-multiselect-summary]');
                    var searchUrl = component.dataset.searchUrl;
                    var minChars = parseInt(component.dataset.minChars || '2', 10);
                    var emptyText = component.dataset.emptyText || 'No options found.';
                    var debounceTimer = null;
                    var abortController = null;

                    function selectedValues() {
                        return Array.from(selectedWrap.querySelectorAll('[data-remote-multiselect-hidden]')).map(function (input) {
                            return String(input.value);
                        });
                    }

                    function updateSummary() {
                        var count = selectedValues().length;
                        summary.textContent = count ? count + ' selected' : 'None selected';
                    }

                    function setMessage(text) {
                        resultsWrap.innerHTML = '';
                        message = document.createElement('div');
                        message.className = 'px-3 py-6 text-center text-sm text-zinc-500';
                        message.dataset.remoteMultiselectMessage = '';
                        message.textContent = text;
                        resultsWrap.appendChild(message);
                    }

                    function addSelection(value, label) {
                        value = String(value);
                        if (selectedValues().indexOf(value) !== -1) return;

                        var pill = document.createElement('span');
                        pill.className = 'inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-sm text-emerald-100';
                        pill.dataset.remoteMultiselectPill = '';
                        pill.dataset.value = value;

                        var text = document.createTextNode(label + ' ');
                        var remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'text-emerald-200 hover:text-white';
                        remove.dataset.remoteMultiselectRemove = '';
                        remove.setAttribute('aria-label', 'Remove ' + label);
                        remove.innerHTML = '&times;';

                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = @json($name);
                        hidden.value = value;
                        hidden.dataset.remoteMultiselectHidden = '';

                        pill.appendChild(text);
                        pill.appendChild(remove);
                        pill.appendChild(hidden);
                        selectedWrap.appendChild(pill);
                        updateSummary();
                    }

                    function renderResults(items) {
                        resultsWrap.innerHTML = '';

                        if (!items.length) {
                            setMessage(emptyText);
                            return;
                        }

                        var selected = selectedValues();
                        items.forEach(function (item) {
                            var value = String(item.id || item.value);
                            var label = item.text || item.label;
                            var isSelected = selected.indexOf(value) !== -1;

                            var button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-white hover:bg-zinc-700/70 cursor-pointer';
                            button.dataset.value = value;
                            button.dataset.label = label;
                            var labelSpan = document.createElement('span');
                            labelSpan.textContent = label;
                            var stateSpan = document.createElement('span');
                            stateSpan.className = 'text-xs text-zinc-400';
                            stateSpan.textContent = isSelected ? 'Selected' : 'Add';
                            button.appendChild(labelSpan);
                            button.appendChild(stateSpan);
                            button.disabled = isSelected;
                            if (isSelected) button.classList.add('opacity-60');

                            button.addEventListener('click', function () {
                                addSelection(value, label);
                                button.disabled = true;
                                button.classList.add('opacity-60');
                                stateSpan.textContent = 'Selected';
                            });

                            resultsWrap.appendChild(button);
                        });
                    }

                    function search() {
                        var query = (input.value || '').trim();

                        if (query.length < minChars) {
                            setMessage('Type at least ' + minChars + ' characters to search.');
                            return;
                        }

                        if (abortController) abortController.abort();
                        abortController = new AbortController();
                        setMessage('Searching...');

                        fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                            headers: { 'Accept': 'application/json' },
                            signal: abortController.signal
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (data) { renderResults(data.results || data || []); })
                            .catch(function (error) {
                                if (error.name !== 'AbortError') setMessage('Search failed. Please try again.');
                            });
                    }

                    input.addEventListener('input', function () {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(search, 300);
                    });

                    selectedWrap.addEventListener('click', function (event) {
                        var remove = event.target.closest('[data-remote-multiselect-remove]');
                        if (!remove) return;

                        remove.closest('[data-remote-multiselect-pill]').remove();
                        updateSummary();
                        if ((input.value || '').trim().length >= minChars) search();
                    });

                    updateSummary();
                });
            });
        </script>
    @endpush
@endonce

