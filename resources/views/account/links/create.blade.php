@extends('layouts.landing')
@section('title', 'Add Link/Page - My Leader Kenya')
@push('styles')
@include('account.partials.news-styles')
<style>.news-account-layout{grid-template-columns:280px minmax(0,1fr)}@media(max-width:900px){.news-account-layout{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<div class="flag-stripe"></div>@include('components.frontend-nav')
<main class="news-account-shell"><div class="news-account-layout">
@include('components.my-account-sidebar')
<section class="news-account-content">
<p class="news-kicker">My Account</p><h1 class="news-title">Add a link or page</h1><p class="news-subtitle">Add a Facebook group, WhatsApp group or web page for a county, party or aspirant. It stays private until an administrator approves it.</p>
<div class="news-actions"><a class="news-button" href="{{ route('account.links.index') }}">&larr; My Links</a></div>
@if($errors->any())<div class="news-alert" style="border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fecaca">{{ $errors->first() }}</div>@endif
<form class="news-form" method="POST" action="{{ route('account.links.store') }}">@csrf
<div class="news-form-grid">
<div class="news-field"><label for="platform">Link platform *</label><select id="platform" name="platform" required>
<option value="">Select a platform</option>
@foreach($platforms as $value => $label)
<option value="{{ $value }}" @selected(old('platform') === $value)>{{ $label }}</option>
@endforeach
</select>@error('platform')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="url">Link *</label><input id="url" type="url" name="url" value="{{ old('url') }}" maxlength="2048" required placeholder="https://www.facebook.com/share/g/...">@error('url')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="county_id">County *</label><select id="county_id" name="county_id" required>
<option value="">Select a county</option>
@foreach($counties as $county)
<option value="{{ $county->id }}" @selected((int) old('county_id') === $county->id)>{{ $county->name }}</option>
@endforeach
</select><p class="news-help">The link will appear on this county's page and on its aspirants' profiles.</p>@error('county_id')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="constituency_id">Constituency</label><select id="constituency_id" name="constituency_id" data-cascade="constituency" data-parent="county_id" disabled>
<option value="">Select a constituency</option>
</select><p class="news-help">Optional. Pick a county first.</p>@error('constituency_id')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="ward_id">Ward</label><select id="ward_id" name="ward_id" data-cascade="ward" data-parent="constituency_id" disabled>
<option value="">Select a ward</option>
</select><p class="news-help">Optional.</p>@error('ward_id')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field"><label for="political_party_id">Political party</label><select id="political_party_id" name="political_party_id">
<option value="">Select a party</option>
@foreach($politicalParties as $party)
<option value="{{ $party->id }}" @selected((int) old('political_party_id') === $party->id)>{{ $party->abbreviation ?: $party->name }}</option>
@endforeach
</select><p class="news-help">Optional.</p>@error('political_party_id')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full">
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
@error('candidate_id')<p class="news-error">{{ $message }}</p>@enderror
</div>
<div class="news-field"><label for="followers">Followers</label><input id="followers" type="number" name="followers" value="{{ old('followers') }}" min="0" max="2000000000" step="1" placeholder="e.g. 12500">@error('followers')<p class="news-error">{{ $message }}</p>@enderror</div>
<div class="news-field full"><label for="comment">Comment</label><textarea id="comment" name="comment" rows="3" maxlength="1000" placeholder="A short note about this group or page...">{{ old('comment') }}</textarea>@error('comment')<p class="news-error">{{ $message }}</p>@enderror</div>
</div>
<button class="news-submit" type="submit">Submit for administrator review</button>
</form>
</section></div></main>
@endsection

@push('scripts')
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
@endpush