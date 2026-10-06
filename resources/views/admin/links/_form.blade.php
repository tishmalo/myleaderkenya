{{-- Shared add/edit form for Pages & Links. Expects $form (action, method,
     submit_label, note), $link (ResourceLink or null) and the option lists
     from UserLinkService::formData(). --}}
@php
    $effectiveCountyId = (int) old('county_id', $link?->county_id ?? '');
    $effectiveConstituencyId = (int) old('constituency_id', $link?->constituency_id ?? '');
@endphp
<div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
    <form method="POST" action="{{ $form['action'] }}">
        @csrf
        @if($form['method'] !== 'POST') @method($form['method']) @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="platform">Link platform <span class="text-red-500">*</span></label>
                <select id="platform" name="platform" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select a platform</option>
                    @foreach($platforms as $value => $label)
                        <option value="{{ $value }}" @selected(old('platform', $link?->platform) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('platform')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="title">Title <span class="text-red-500">*</span></label>
                <input id="title" type="text" name="title" value="{{ old('title', $link?->title) }}" maxlength="255" required placeholder="e.g. Manaichi Linda"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                <p class="mt-2 text-xs text-zinc-500">The name visitors see and click, e.g. "Manaichi Facebook page".</p>
                @error('title')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2" for="url">Link <span class="text-red-500">*</span></label>
                <input id="url" type="url" name="url" value="{{ old('url', $link?->url) }}" maxlength="2048" required placeholder="https://www.facebook.com/share/g/..."
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('url')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="county_id">County <span class="text-red-500">*</span></label>
                <select id="county_id" name="county_id" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select a county</option>
                    @foreach($counties as $county)
                        <option value="{{ $county->id }}" @selected($effectiveCountyId === $county->id)>{{ $county->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-zinc-500">The link will appear on this county's page and on its aspirants' profiles.</p>
                @error('county_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="constituency_id">Constituency</label>
                <select id="constituency_id" name="constituency_id" @disabled($effectiveCountyId === 0) class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white disabled:opacity-50">
                    <option value="">Select a constituency</option>
                    @foreach($constituencies->where('county_id', $effectiveCountyId) as $constituency)
                        <option value="{{ $constituency->id }}" @selected($effectiveConstituencyId === $constituency->id)>{{ $constituency->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-zinc-500">Optional. Pick a county first.</p>
                @error('constituency_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="ward_id">Ward</label>
                <select id="ward_id" name="ward_id" @disabled($effectiveConstituencyId === 0) class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white disabled:opacity-50">
                    <option value="">Select a ward</option>
                    @foreach($wards->where('constituency_id', $effectiveConstituencyId) as $ward)
                        <option value="{{ $ward->id }}" @selected((int) old('ward_id', $link?->ward_id ?? '') === $ward->id)>{{ $ward->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-zinc-500">Optional.</p>
                @error('ward_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="political_party_id">Political party</label>
                <select id="political_party_id" name="political_party_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select a party</option>
                    @foreach($politicalParties as $party)
                        <option value="{{ $party->id }}" @selected((int) old('political_party_id', $link?->political_party_id ?? '') === $party->id)>{{ $party->abbreviation ?: $party->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-zinc-500">Optional.</p>
                @error('political_party_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <x-aspirant-search
                    name="candidate_id"
                    :search-url="route('aspirants.search')"
                    :selected-candidate="$selectedCandidate"
                    label="Aspirant (optional)"
                    placeholder="Search aspirants by name or nickname..."
                    empty-text="No approved aspirants match your search."
                    selection-note="This links the page to this aspirant's public profile."
                    help="Leave empty to target the whole county, party, constituency or ward."
                />
                @error('candidate_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="followers">Followers</label>
                <input id="followers" type="number" name="followers" value="{{ old('followers', $link?->followers) }}" min="0" max="2000000000" step="1" placeholder="e.g. 12500"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                @error('followers')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2" for="comment">Comment</label>
                <textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="A short note about this group or page..."
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500">{{ old('comment', $link?->comment) }}</textarea>
                @error('comment')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-4">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 px-8 py-3 rounded-2xl text-sm font-medium text-white">
                {{ $form['submit_label'] }}
            </button>
            <p class="text-xs text-zinc-500">{{ $form['note'] }}</p>
        </div>
    </form>
</div>

<script>
(function () {
    var source = {
        constituencies: @json($constituencies->map(fn ($constituency) => ['id' => $constituency->id, 'name' => $constituency->name, 'county_id' => $constituency->county_id])),
        wards: @json($wards->map(fn ($ward) => ['id' => $ward->id, 'name' => $ward->name, 'constituency_id' => $ward->constituency_id])),
    };

    function reset(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        select.disabled = true;
    }

    document.getElementById('county_id').addEventListener('change', function (event) {
        var countyId = event.target.value;
        var constituency = document.getElementById('constituency_id');
        var ward = document.getElementById('ward_id');

        reset(ward, 'Select a ward');
        reset(constituency, 'Select a constituency');

        if (!countyId) {
            return;
        }

        constituency.disabled = false;
        source.constituencies.forEach(function (constituencyOption) {
            if (String(constituencyOption.county_id) !== String(countyId)) {
                return;
            }
            var option = document.createElement('option');
            option.value = constituencyOption.id;
            option.textContent = constituencyOption.name;
            constituency.appendChild(option);
        });
    });

    document.getElementById('constituency_id').addEventListener('change', function (event) {
        var constituencyId = event.target.value;
        var ward = document.getElementById('ward_id');

        reset(ward, 'Select a ward');

        if (!constituencyId) {
            return;
        }

        ward.disabled = false;
        source.wards.forEach(function (wardOption) {
            if (String(wardOption.constituency_id) !== String(constituencyId)) {
                return;
            }
            var option = document.createElement('option');
            option.value = wardOption.id;
            option.textContent = wardOption.name;
            ward.appendChild(option);
        });
    });
})();
</script>
