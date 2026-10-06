<div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
    <form method="POST" action="{{ $form['action'] }}" data-poll-form>
        @csrf
        @if($form['editing']) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2">Question <span class="text-red-500">*</span></label>
                <input type="text" name="question" required maxlength="255" data-poll-type-toggle
                       value="{{ $form['question'] }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('question')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Poll Type</label>
                <select name="poll_type" data-poll-type-select
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="words" @selected($form['selected_type'] === 'words')>Words — plain text options</option>
                    <option value="political" @selected($form['selected_type'] === 'political')>Political — aspirant cards</option>
                </select>
                <p class="mt-2 text-xs text-zinc-500">Political options are shown as aspirant cards using their current profile photo, position, area and party.</p>
                @error('poll_type')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Status</label>
                <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="draft" @selected($form['status_selected'] === 'draft')>Draft — hidden from the homepage</option>
                    <option value="active" @selected($form['status_selected'] === 'active')>Active — show on the homepage</option>
                    <option value="closed" @selected($form['status_selected'] === 'closed')>Closed — stop accepting votes</option>
                </select>
                @error('status')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Opens (optional)</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ $form['starts_at_value'] }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Leave blank to open as soon as the poll is active.</p>
                @error('starts_at')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Closes <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="ends_at" required
                       value="{{ $form['ends_at_value'] }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Voting stops and results unlock at this moment.</p>
                @error('ends_at')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6">
            <label class="flex items-start gap-3 rounded-2xl border border-zinc-800 bg-zinc-950/40 p-4 cursor-pointer">
                <input type="checkbox" name="reveal_results" value="1" class="mt-1 w-4 h-4 accent-emerald-500"
                       @checked($form['reveal_results_checked'])>
                <span>
                    <span class="block text-sm text-white">Reveal results to the public when the poll closes</span>
                    <span class="block text-xs text-zinc-500 mt-1">When off, the public sees that a poll ran and how many people voted, but never the breakdown. You still see everything.</span>
                </span>
            </label>
        </div>

        <div class="mt-4">
            {{-- Hidden fallback so unchecking the box actually saves as off. --}}
            <input type="hidden" name="show_results_to_voters" value="0">
            <label class="flex items-start gap-3 rounded-2xl border border-zinc-800 bg-zinc-950/40 p-4 cursor-pointer">
                <input type="checkbox" name="show_results_to_voters" value="1" class="mt-1 w-4 h-4 accent-emerald-500"
                       @checked($form['show_results_to_voters_checked'])>
                <span>
                    <span class="block text-sm text-white">Show results to voters immediately after they vote</span>
                    <span class="block text-xs text-zinc-500 mt-1">When off, voters see a confirmation but no breakdown until results go public. They can still change their vote while the poll is open.</span>
                </span>
            </label>
        </div>

        <div class="mt-8">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm text-zinc-400">Options <span class="text-red-500">*</span></label>
                <button type="button" data-poll-add-option
                        class="bg-zinc-800 hover:bg-zinc-700 px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add option
                </button>
            </div>

            <div class="mb-6 rounded-2xl border border-zinc-800 bg-zinc-950/40 p-4">
                <label class="block text-sm text-zinc-400 mb-1">Audience</label>
                @if($form['audience']['draft'])
                    <p class="text-sm text-zinc-300">{{ $form['audience']['label'] }}</p>
                @else
                    <p class="text-sm text-emerald-400">{{ $form['audience']['label'] }}</p>
                    @if($form['audience']['scope_key'] === 'county' || $form['audience']['scope_key'] === 'constituency' || $form['audience']['scope_key'] === 'ward')
                        <p class="mt-1 text-xs text-zinc-500">
                            County: {{ $form['audience']['county'] }}
                            @if($form['audience']['constituency']) · Constituency: {{ $form['audience']['constituency'] }} @endif
                            @if($form['audience']['ward']) · Ward: {{ $form['audience']['ward'] }} @endif
                        </p>
                    @endif
                @endif
            </div>

            @error('options')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror
            @error('options.*')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror

            {{-- Bulk aspirant picker, shown only for political polls. --}}
            <div data-poll-picker class="hidden mb-6 rounded-2xl border border-zinc-800 bg-zinc-950/40 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-2">Position</label>
                        <select name="_picker_position" data-picker-position
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                            <option value="">All positions</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-2">County</label>
                        <select name="_picker_county" data-picker-county
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                            <option value="">All counties</option>
                        </select>
                    </div>
                    <div data-picker-constituency-wrap class="hidden">
                        <label class="block text-xs text-zinc-400 mb-2">Constituency</label>
                        <select name="_picker_constituency" data-picker-constituency
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                            <option value="">All constituencies</option>
                        </select>
                    </div>
                    <div data-picker-ward-wrap class="hidden">
                        <label class="block text-xs text-zinc-400 mb-2">Ward</label>
                        <select name="_picker_ward" data-picker-ward
                                class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                            <option value="">All wards</option>
                        </select>
                    </div>
                </div>

                <div data-picker-results class="mt-4 max-h-72 overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-950/60 p-2">
                    <p data-picker-empty class="text-sm text-zinc-500 p-3">Choose a position or a location to list aspirants, then tick the ones to add.</p>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm text-zinc-400" data-picker-selected>0 aspirants selected</span>
                    <button type="button" data-picker-add
                            class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-xl text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed"
                            disabled>
                        Add selected
                    </button>
                </div>
            </div>

            <div data-poll-options class="grid gap-3">
                @foreach($form['existing_options'] as $index => $option)
                    <div class="poll-option-row grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-start" data-poll-option-row>
                        <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] ?? '' }}" data-poll-option-id>
                        <input type="hidden" name="options[{{ $index }}][candidate_id]" value="{{ $option['candidate_id'] ?? '' }}" data-poll-candidate-id>

                        <div class="min-w-0">
                            <input type="text" name="options[{{ $index }}][label]" maxlength="255" placeholder="Option label"
                                   value="{{ $option['label'] ?? '' }}"
                                   data-poll-label
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">

                            <div data-poll-chip class="hidden items-center gap-3 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 font-semibold text-emerald-300"
                                      data-poll-chip-initial>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($option['candidate_name'] ?? '', 0, 1)) }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-white" data-poll-chip-name>{{ $option['candidate_name'] ?? '' }}</span>
                                    <span class="block truncate text-xs text-zinc-400" data-poll-chip-badge>{{ $option['candidate_badge'] ?? '' }}</span>
                                </span>
                            </div>
                        </div>

                        <button type="button" data-poll-remove-option
                                class="text-red-400 hover:text-red-500 px-3 py-3">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-10">
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg">
                {{ $form['submit_label'] }}
            </button>
        </div>
    </form>
</div>

@once
@push('scripts')
<script>
(function () {
    var forms = document.querySelectorAll('[data-poll-form]');

    if (!forms.length) return;

    var endpoint = @json($form['aspirant_picker_url']);

    function requestPath(filters) {
        var params = new URLSearchParams();
        Object.keys(filters).forEach(function (key) {
            if (filters[key]) params.append(key, filters[key]);
        });
        return endpoint + (params.toString() ? '?' + params.toString() : '');
    }

    forms.forEach(function (form) {
        var list = form.querySelector('[data-poll-options]');
        var typeSelect = form.querySelector('[data-poll-type-select]');
        var picker = form.querySelector('[data-poll-picker]');
        var addButton = form.querySelector('[data-poll-add-option]');
        var positionSelect = form.querySelector('[data-picker-position]');
        var countySelect = form.querySelector('[data-picker-county]');
        var constituencySelect = form.querySelector('[data-picker-constituency]');
        var wardSelect = form.querySelector('[data-picker-ward]');
        var constituencyWrap = form.querySelector('[data-picker-constituency-wrap]');
        var wardWrap = form.querySelector('[data-picker-ward-wrap]');
        var results = form.querySelector('[data-picker-results]');
        var resultsEmpty = form.querySelector('[data-picker-empty]');
        var selectedLabel = form.querySelector('[data-picker-selected]');
        var addSelected = form.querySelector('[data-picker-add]');

        var counter = list.querySelectorAll('[data-poll-option-row]').length;
        var cachedCandidates = [];

        function reindex() {
            list.querySelectorAll('[data-poll-option-row]').forEach(function (row, index) {
                row.querySelectorAll('[name]').forEach(function (field) {
                    field.name = field.name.replace(/options\[\d+\]/, 'options[' + index + ']');
                });
            });
        }

        function setRowCandidate(row, candidate) {
            row.querySelector('[data-poll-candidate-id]').value = candidate.id;
            var name = candidate.name;
            row.querySelector('[data-poll-label]').value = name;
            row.querySelector('[data-poll-chip-name]').textContent = name;
            row.querySelector('[data-poll-chip-badge]').textContent = candidate.badge || '';
            row.querySelector('[data-poll-chip-initial]').textContent = name.charAt(0).toUpperCase();
        }

        function applyType() {
            var political = typeSelect.value === 'political';

            picker.classList.toggle('hidden', !political);
            addButton.classList.toggle('hidden', political);

            list.querySelectorAll('[data-poll-option-row]').forEach(function (row) {
                var hasCandidate = row.querySelector('[data-poll-candidate-id]').value !== '';
                var chip = row.querySelector('[data-poll-chip]');
                var label = row.querySelector('[data-poll-label]');

                if (political && hasCandidate) {
                    chip.classList.remove('hidden');
                    chip.classList.add('flex');
                    label.classList.add('hidden');
                    label.disabled = true;
                } else {
                    chip.classList.add('hidden');
                    chip.classList.remove('flex');
                    label.classList.remove('hidden');
                    label.disabled = false;
                }
            });

            if (political) loadPickerContext();
        }

        function loadPickerContext() {
            if (positionSelect.dataset.loaded) return;
            fetch(endpoint)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    positionSelect.dataset.loaded = '1';
                    data.positions.forEach(function (position) {
                        var option = document.createElement('option');
                        option.value = position.id;
                        option.textContent = position.name;
                        positionSelect.appendChild(option);
                    });
                    data.counties.forEach(function (county) {
                        var option = document.createElement('option');
                        option.value = county;
                        option.textContent = county;
                        countySelect.appendChild(option);
                    });
                });
        }

        function renderLocationOptions(select, names) {
            select.querySelectorAll('option:not(:first-child)').forEach(function (option) { option.remove(); });
            names.forEach(function (name) {
                var option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                select.appendChild(option);
            });
        }

        function currentFilters() {
            return {
                position_id: positionSelect.value,
                county: countySelect.value,
                constituency: constituencySelect.value,
                ward: wardSelect.value,
            };
        }

        function fetchResults() {
            var filters = currentFilters();

            resultsEmpty.textContent = 'Loading aspirants…';

            fetch(requestPath(filters))
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    renderLocationOptions(constituencySelect, data.constituencies);
                    renderLocationOptions(wardSelect, data.wards);
                    constituencyWrap.classList.toggle('hidden', !filters.county);
                    wardWrap.classList.toggle('hidden', !filters.constituency);

                    cachedCandidates = data.candidates;
                    renderCandidates(cachedCandidates, filters);
                });
        }

        function renderCandidates(candidates) {
            results.querySelectorAll('[data-picker-candidate]').forEach(function (node) { node.remove(); });
            resultsEmpty.style.display = candidates.length ? 'none' : '';

            candidates.forEach(function (candidate) {
                var added = list.querySelector('[data-poll-candidate-id][value="' + candidate.id + '"]');

                var row = document.createElement('label');
                row.className = 'flex items-start gap-3 rounded-xl p-3 hover:bg-zinc-800/60 cursor-pointer' + (added ? ' opacity-40 cursor-not-allowed' : '');
                row.setAttribute('data-picker-candidate', '');
                if (added) row.style.pointerEvents = 'none';

                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = candidate.id;
                checkbox.className = 'mt-1 w-4 h-4 accent-emerald-500';
                checkbox.setAttribute('data-picker-checkbox', '');

                var text = document.createElement('span');
                text.className = 'min-w-0';
                var name = document.createElement('span');
                name.className = 'block font-medium text-white text-sm';
                name.textContent = candidate.name;
                var badge = document.createElement('span');
                badge.className = 'block text-xs text-zinc-400 truncate';
                badge.textContent = candidate.badge || '';
                text.appendChild(name);
                text.appendChild(badge);

                row.appendChild(checkbox);
                row.appendChild(text);
                results.appendChild(row);
            });

            updateSelectedCount();
        }

        function updateSelectedCount() {
            var count = results.querySelectorAll('[data-picker-checkbox]:checked').length;
            selectedLabel.textContent = count === 1 ? '1 aspirant selected' : count + ' aspirants selected';
            if (count > 0) {
                addSelected.classList.remove('disabled');
                var rooms = 12 - list.querySelectorAll('[data-poll-option-row]').length;
                addSelected.disabled = count === 0 || rooms <= 0;
                addSelected.textContent = 'Add selected' + (count > 0 ? ' (' + count + ')' : '');
            } else {
                addSelected.disabled = true;
                addSelected.textContent = 'Add selected';
            }
        }

        function buildRow() {
            var row = document.createElement('div');
            row.className = 'poll-option-row grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-start';
            row.setAttribute('data-poll-option-row', '');

            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'options[' + counter + '][id]';
            idInput.setAttribute('data-poll-option-id', '');

            var candidateId = document.createElement('input');
            candidateId.type = 'hidden';
            candidateId.name = 'options[' + counter + '][candidate_id]';
            candidateId.setAttribute('data-poll-candidate-id', '');

            var content = document.createElement('div');
            content.className = 'min-w-0';

            var label = document.createElement('input');
            label.type = 'text';
            label.name = 'options[' + counter + '][label]';
            label.maxLength = 255;
            label.placeholder = 'Option label';
            label.setAttribute('data-poll-label', '');
            label.className = 'w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500';

            var chip = document.createElement('div');
            chip.className = 'hidden items-center gap-3 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3';
            chip.setAttribute('data-poll-chip', '');
            var initial = document.createElement('span');
            initial.className = 'flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 font-semibold text-emerald-300';
            initial.setAttribute('data-poll-chip-initial', '');
            var chipText = document.createElement('span');
            chipText.className = 'min-w-0';
            var chipName = document.createElement('span');
            chipName.className = 'block truncate font-medium text-white';
            chipName.setAttribute('data-poll-chip-name', '');
            var chipBadge = document.createElement('span');
            chipBadge.className = 'block truncate text-xs text-zinc-400';
            chipBadge.setAttribute('data-poll-chip-badge', '');
            chipText.appendChild(chipName);
            chipText.appendChild(chipBadge);
            chip.appendChild(initial);
            chip.appendChild(chipText);
            content.appendChild(label);
            content.appendChild(chip);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('data-poll-remove-option', '');
            remove.className = 'text-red-400 hover:text-red-500 px-3 py-3';
            remove.innerHTML = '<i class="fas fa-trash"></i>';

            row.append(idInput, candidateId, content, remove);
            list.appendChild(row);
            counter++;
            return row;
        }

        addButton.addEventListener('click', function () {
            if (counter >= 12) return;
            buildRow();
            applyType();
        });

        addSelected.addEventListener('click', function () {
            var checked = Array.prototype.slice.call(results.querySelectorAll('[data-picker-checkbox]:checked'));
            var rooms = 12 - list.querySelectorAll('[data-poll-option-row]').length;

            checked.slice(0, rooms).forEach(function (checkbox) {
                var candidate = cachedCandidates.find(function (candidate) {
                    return String(candidate.id) === String(checkbox.value);
                });
                if (!candidate) return;
                var row = buildRow();
                setRowCandidate(row, candidate);
            });

            reindex();
            applyType();
            fetchResults();
        });

        list.addEventListener('click', function (event) {
            var button = event.target.closest('[data-poll-remove-option]');
            if (!button) return;
            var rows = list.querySelectorAll('[data-poll-option-row]');
            if (rows.length <= 2) return;
            button.closest('[data-poll-option-row]').remove();
            counter--;
            reindex();
            if (!picker.classList.contains('hidden')) fetchResults();
        });

        results.addEventListener('change', updateSelectedCount);

        [positionSelect, countySelect, constituencySelect, wardSelect].forEach(function (select) {
            select.addEventListener('change', fetchResults);
        });

        typeSelect.addEventListener('change', applyType);
        applyType();
    });
})();
</script>
@endpush
@endonce