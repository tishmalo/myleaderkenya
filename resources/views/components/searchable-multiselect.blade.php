@props([
    'name',
    'label',
    'options' => [],
    'selected' => [],
    'placeholder' => 'Search...',
    'emptyText' => 'No options found.',
])

@php
    $selectedValues = collect(old(str_replace('[]', '', $name), $selected))->map(fn ($value) => (string) $value)->all();
    $componentId = 'searchable-multiselect-' . md5($name . $label . uniqid('', true));
@endphp

<div class="space-y-2" data-searchable-multiselect id="{{ $componentId }}">
    <label class="block text-sm text-zinc-400">{{ $label }}</label>

    <div class="rounded-2xl border border-zinc-700 bg-zinc-800 overflow-hidden focus-within:border-emerald-500 transition-colors">
        <div class="p-3 border-b border-zinc-700">
            <input type="search"
                   data-searchable-multiselect-input
                   placeholder="{{ $placeholder }}"
                   class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-4 py-2 text-white placeholder:text-zinc-500 focus:outline-none focus:border-emerald-500">
        </div>

        <div class="max-h-64 overflow-y-auto p-2 space-y-1" data-searchable-multiselect-list>
            @foreach($options as $option)
                @php
                    $value = (string) data_get($option, 'value');
                    $optionLabel = data_get($option, 'label');
                    $checked = in_array($value, $selectedValues, true);
                @endphp
                <label class="flex items-center gap-3 rounded-xl px-3 py-2 text-white hover:bg-zinc-700/70 cursor-pointer"
                       data-searchable-multiselect-option
                       data-search-text="{{ Str::lower($optionLabel) }}">
                    <input type="checkbox"
                           name="{{ $name }}"
                           value="{{ $value }}"
                           class="h-4 w-4 rounded border-zinc-600 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                           {{ $checked ? 'checked' : '' }}>
                    <span>{{ $optionLabel }}</span>
                </label>
            @endforeach

            <div class="hidden px-3 py-6 text-center text-sm text-zinc-500" data-searchable-multiselect-empty>
                {{ $emptyText }}
            </div>
        </div>
    </div>

    <div class="text-xs text-zinc-500" data-searchable-multiselect-summary></div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-searchable-multiselect]').forEach(function (component) {
                    var input = component.querySelector('[data-searchable-multiselect-input]');
                    var options = Array.from(component.querySelectorAll('[data-searchable-multiselect-option]'));
                    var empty = component.querySelector('[data-searchable-multiselect-empty]');
                    var summary = component.querySelector('[data-searchable-multiselect-summary]');

                    function updateSummary() {
                        var count = options.filter(function (option) {
                            var checkbox = option.querySelector('input[type="checkbox"]');
                            return checkbox && checkbox.checked;
                        }).length;

                        summary.textContent = count ? count + ' selected' : 'None selected';
                    }

                    function filterOptions() {
                        var query = (input.value || '').trim().toLowerCase();
                        var visible = 0;

                        options.forEach(function (option) {
                            var haystack = option.dataset.searchText || '';
                            var match = haystack.indexOf(query) !== -1;
                            option.classList.toggle('hidden', !match);
                            if (match) visible++;
                        });

                        empty.classList.toggle('hidden', visible !== 0);
                    }

                    input.addEventListener('input', filterOptions);
                    options.forEach(function (option) {
                        var checkbox = option.querySelector('input[type="checkbox"]');
                        if (checkbox) checkbox.addEventListener('change', updateSummary);
                    });

                    updateSummary();
                    filterOptions();
                });
            });
        </script>
    @endpush
@endonce