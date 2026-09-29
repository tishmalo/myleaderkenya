@php
    $editing = isset($poll) && $poll;
    $selectedType = old('poll_type', $poll->poll_type ?? 'words');
    // old() arguments are evaluated eagerly, so the existing rows cannot be
    // passed as its default here: on the create screen $poll is null and
    // $poll->options would throw. Resolve them separately instead.
    $existingOptions = old('options');
    if (is_null($existingOptions)) {
        $existingOptions = $editing
            ? $poll->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'candidate_id' => $option->candidate_id,
            ])->all()
            : [];
    }
    if (empty($existingOptions)) {
        $existingOptions = [['id' => null, 'label' => '', 'candidate_id' => null], ['id' => null, 'label' => '', 'candidate_id' => null]];
    }
    $candidateGroups = $candidates->groupBy(fn ($candidate) => $candidate->position->name ?? 'Aspirants');
    // Precomputed here rather than inline in the script: Blade's @json()
    // directive cannot parse a nested array expression, and the hex flags
    // keep candidate names from closing the <script> block.
    $candidateGroupsJson = json_encode(
        $candidateGroups->map(fn ($group, $name) => [
            'label' => $name,
            'candidates' => $group->map(fn ($candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->name.($candidate->politicalParty?->abbreviation ? ' ('.$candidate->politicalParty->abbreviation.')' : ''),
            ])->values(),
        ])->values(),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );
@endphp

<div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
    <form method="POST" action="{{ $editing ? route('polls.update', $poll) : route('polls.store') }}" data-poll-form>
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2">Question <span class="text-red-500">*</span></label>
                <input type="text" name="question" required maxlength="255" data-poll-type-toggle
                       value="{{ old('question', $poll->question ?? '') }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('question')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Poll Type</label>
                <select name="poll_type" data-poll-type-select
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="words" @selected($selectedType === 'words')>Words — plain text options</option>
                    <option value="political" @selected($selectedType === 'political')>Political — aspirant cards</option>
                </select>
                <p class="mt-2 text-xs text-zinc-500">Political options are shown as aspirant cards using their current profile photo, position, area and party.</p>
                @error('poll_type')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Status</label>
                <select name="status" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="draft" @selected(old('status', $poll->status ?? 'draft') === 'draft')>Draft — hidden from the homepage</option>
                    <option value="active" @selected(old('status', $poll->status ?? 'draft') === 'active')>Active — show on the homepage</option>
                    <option value="closed" @selected(old('status', $poll->status ?? 'draft') === 'closed')>Closed — stop accepting votes</option>
                </select>
                @error('status')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Opens (optional)</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at', isset($poll->starts_at) && $poll->starts_at ? $poll->starts_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Leave blank to open as soon as the poll is active.</p>
                @error('starts_at')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Closes <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="ends_at" required
                       value="{{ old('ends_at', isset($poll->ends_at) && $poll->ends_at ? $poll->ends_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Voting stops and results unlock at this moment.</p>
                @error('ends_at')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6">
            <label class="flex items-start gap-3 rounded-2xl border border-zinc-800 bg-zinc-950/40 p-4 cursor-pointer">
                <input type="checkbox" name="reveal_results" value="1" class="mt-1 w-4 h-4 accent-emerald-500"
                       @checked(old('reveal_results', $poll->reveal_results ?? true))>
                <span>
                    <span class="block text-sm text-white">Reveal results to the public when the poll closes</span>
                    <span class="block text-xs text-zinc-500 mt-1">When off, the public sees that a poll ran and how many people voted, but never the breakdown. You still see everything.</span>
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

            @error('options')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror
            @error('options.*')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror

            <div data-poll-options class="grid gap-3">
                @foreach($existingOptions as $index => $option)
                    <div class="poll-option-row grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-start" data-poll-option-row>
                        <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] ?? '' }}" data-poll-option-id>

                        <div>
                            <input type="text" name="options[{{ $index }}][label]" maxlength="255" placeholder="Option label"
                                   value="{{ $option['label'] ?? '' }}"
                                   data-poll-label
                                   class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                        </div>

                        <div data-poll-candidate-wrap class="hidden">
                            <select name="options[{{ $index }}][candidate_id]" data-poll-candidate
                                    class="w-full md:w-80 bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                                <option value="">Select an aspirant</option>
                                @foreach($candidateGroups as $groupName => $groupCandidates)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($groupCandidates as $candidate)
                                            <option value="{{ $candidate->id }}" @selected((int) ($option['candidate_id'] ?? 0) === (int) $candidate->id)>
                                                {{ $candidate->name }}{{ $candidate->politicalParty?->abbreviation ? ' ('.$candidate->politicalParty->abbreviation.')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
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
                {{ $editing ? 'Save Poll' : 'Create Poll' }}
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

    var candidateMarkup = {!! $candidateGroupsJson !!};

    forms.forEach(function (form) {
        var list = form.querySelector('[data-poll-options]');
        var typeSelect = form.querySelector('[data-poll-type-select]');
        var counter = list.querySelectorAll('[data-poll-option-row]').length;

        function reindex() {
            list.querySelectorAll('[data-poll-option-row]').forEach(function (row, index) {
                row.querySelectorAll('[name]').forEach(function (field) {
                    field.name = field.name.replace(/options\[\d+\]/, 'options[' + index + ']');
                });
            });
        }

        function applyType() {
            var political = typeSelect.value === 'political';
            form.querySelectorAll('[data-poll-candidate-wrap]').forEach(function (wrap) {
                wrap.classList.toggle('hidden', !political);
            });
            form.querySelectorAll('[data-poll-label]').forEach(function (input) {
                input.closest('div').classList.toggle('hidden', political);
                input.disabled = political;
            });
        }

        function buildRow() {
            var row = document.createElement('div');
            row.className = 'poll-option-row grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-start';
            row.setAttribute('data-poll-option-row', '');

            var idWrap = document.createElement('div');
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'options[' + counter + '][id]';
            idInput.value = '';
            idInput.setAttribute('data-poll-option-id', '');
            idWrap.appendChild(idInput);

            var labelWrap = document.createElement('div');
            var label = document.createElement('input');
            label.type = 'text';
            label.name = 'options[' + counter + '][label]';
            label.maxLength = 255;
            label.placeholder = 'Option label';
            label.setAttribute('data-poll-label', '');
            label.className = 'w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500';
            labelWrap.appendChild(label);

            var candidateWrap = document.createElement('div');
            candidateWrap.className = 'hidden';
            candidateWrap.setAttribute('data-poll-candidate-wrap', '');
            var select = document.createElement('select');
            select.name = 'options[' + counter + '][candidate_id]';
            select.setAttribute('data-poll-candidate', '');
            select.className = 'w-full md:w-80 bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white';
            var blank = document.createElement('option');
            blank.value = '';
            blank.textContent = 'Select an aspirant';
            select.appendChild(blank);
            candidateMarkup.forEach(function (group) {
                var optgroup = document.createElement('optgroup');
                optgroup.label = group.label;
                group.candidates.forEach(function (candidate) {
                    var option = document.createElement('option');
                    option.value = candidate.id;
                    option.textContent = candidate.name;
                    optgroup.appendChild(option);
                });
                select.appendChild(optgroup);
            });
            candidateWrap.appendChild(select);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('data-poll-remove-option', '');
            remove.className = 'text-red-400 hover:text-red-500 px-3 py-3';
            remove.innerHTML = '<i class="fas fa-trash"></i>';

            row.append(idWrap, labelWrap, candidateWrap, remove);
            list.appendChild(row);
            counter++;
            applyType();
        }

        form.querySelector('[data-poll-add-option]').addEventListener('click', function () {
            if (counter >= 12) return;
            buildRow();
        });

        list.addEventListener('click', function (event) {
            var button = event.target.closest('[data-poll-remove-option]');
            if (!button) return;
            var rows = list.querySelectorAll('[data-poll-option-row]');
            if (rows.length <= 2) return;
            button.closest('[data-poll-option-row]').remove();
            reindex();
        });

        typeSelect.addEventListener('change', applyType);
        applyType();
    });
})();
</script>
@endpush
@endonce
