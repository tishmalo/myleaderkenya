@extends('layouts.app')

@section('page_title', 'Edit Candidate')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-user-edit text-emerald-500"></i>
            Edit Candidate
        </h1>
        <a href="{{ route('candidates.index') }}" class="text-zinc-400 hover:text-white">← Back to Candidates</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('candidates.update', $candidate) }}" method="POST" enctype="multipart/form-data" id="candidateForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $candidate->name) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Nick Name</label>
                    <input type="text" name="nick_name" value="{{ old('nick_name', $candidate->nick_name) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $candidate->phone) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $candidate->email) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <!-- Political Party -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Political Party</label>
                <select name="political_party_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">No Political Party</option>
                    @foreach($politicalParties as $party)
                        <option value="{{ $party->id }}" {{ old('political_party_id', $candidate->political_party_id) == $party->id ? 'selected' : '' }}>
                            {{ $party->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Position -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Position <span class="text-red-500">*</span></label>
                <select name="position_id" id="positionSelect" required
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" {{ $candidate->position_id == $pos->id ? 'selected' : '' }}>
                            {{ $pos->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jurisdiction Fields (Cascading) -->
            <div id="jurisdictionFields" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Populated by JavaScript -->
            </div>

            <!-- Profile Picture -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Profile Picture</label>
                @if($candidate->profile_picture)
                    <div class="mb-3">
                        <img src="{{ Storage::url($candidate->profile_picture) }}" 
                             alt="Profile" class="w-28 h-28 object-cover rounded-2xl border border-zinc-700">
                    </div>
                @endif
                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>

            <!-- About -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">About Candidate</label>
                <textarea name="about" rows="5"
                          class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">{{ old('about', $candidate->about) }}</textarea>
            </div>

            @php($smsSetting = \Illuminate\Support\Facades\Schema::hasTable('candidate_sms_settings') ? $candidate->smsSetting : null)
            <div class="mt-8 border border-zinc-800 rounded-3xl p-6 bg-zinc-950">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                            <i class="fas fa-comment-sms text-emerald-500"></i>
                            Bulk SMS Settings
                        </h2>
                        <p class="text-sm text-zinc-500 mt-1">Infobip username and password are stored encrypted and are only used by this candidate's Bulk SMS workspace.</p>
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm text-zinc-300">
                        <input type="hidden" name="sms_enabled" value="0">
                        <input type="checkbox" name="sms_enabled" value="1" class="rounded border-zinc-700 bg-zinc-800 text-emerald-600" {{ old('sms_enabled', optional($smsSetting)->enabled) ? 'checked' : '' }}>
                        Enabled
                    </label>
                </div>

                <input type="hidden" name="sms_provider" value="infobip">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Infobip Base URL</label>
                        <input type="url" name="sms_base_url" value="{{ old('sms_base_url', optional($smsSetting)->base_url) }}" placeholder="https://xxxxx.api.infobip.com"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Sender Name</label>
                        <input type="text" name="sms_sender_name" value="{{ old('sms_sender_name', optional($smsSetting)->sender_name) }}" placeholder="EGEMEOARDHI"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Username</label>
                        <input type="text" name="sms_username" value="{{ old('sms_username', optional($smsSetting)->username) }}" placeholder="EGEMEOARDHI"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-2">Password</label>
                        <input type="password" name="sms_password" value="" placeholder="{{ $smsSetting && $smsSetting->password ? 'Leave blank to keep existing password' : 'Paste candidate Infobip password' }}"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    </div>
                </div>
            </div>

            <div class="mt-10 flex gap-4">
                <a href="{{ route('candidates.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Update Candidate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const positionSelect = document.getElementById('positionSelect');
    const fieldsContainer = document.getElementById('jurisdictionFields');

    let currentCounty = "{{ $candidate->county ?? '' }}";
    let currentConstituency = "{{ $candidate->constituency ?? '' }}";
    let currentWard = "{{ $candidate->ward ?? '' }}";

    function loadJurisdictionFields(positionName) {
        let html = '';

        const pos = positionName.toLowerCase();

        if (pos.includes('president')) {
            html = `
                <div class="md:col-span-3">
                    <label class="block text-sm text-zinc-400 mb-2">Country</label>
                    <input type="text" name="country" value="Kenya" readonly 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>`;
        } 
        else if (pos.includes('governor')) {
            html = createCountyField();
        } 
        else if (pos.includes('senator') || pos.includes('mp') || pos.includes('woman representative')) {
            html = createCountyField() + createConstituencyField();
        } 
        else if (pos.includes('mca')) {
            html = createCountyField() + createConstituencyField() + createWardField();
        } 
        else {
            html = createCountyField() + createConstituencyField() + createWardField();
        }

        fieldsContainer.innerHTML = html;
        initCascadingDropdowns();
    }

    function createCountyField() {
        return `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>`;
    }

    function createConstituencyField() {
        return `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>`;
    }

    function createWardField() {
        return `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward</label>
                <select name="ward" id="wardSelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Ward</option>
                </select>
            </div>`;
    }

    function optionName(item) {
        return typeof item === 'object' && item !== null ? (item.name || item.label || '') : item;
    }

    function optionId(item) {
        return typeof item === 'object' && item !== null ? (item.id || '') : '';
    }

    function initCascadingDropdowns() {
        const countySelect = document.getElementById('countySelect');
        const constituencySelect = document.getElementById('constituencySelect');
        const wardSelect = document.getElementById('wardSelect');

        if (!countySelect) return;

        // Load Counties
        fetch('/api/counties')
            .then(res => res.json())
            .then(counties => {
                counties.forEach(county => {
                    const opt = new Option(county, county);
                    if (county === currentCounty) opt.selected = true;
                    countySelect.add(opt);
                });

                if (currentCounty && constituencySelect) {
                    loadConstituencies(currentCounty);
                }
            });

        if (countySelect) {
            countySelect.addEventListener('change', function() {
                const county = this.value;
                if (constituencySelect) loadConstituencies(county);
                if (wardSelect) wardSelect.innerHTML = '<option value="">Select Ward</option>';
            });
        }
    }

    function loadConstituencies(county) {
        const constituencySelect = document.getElementById('constituencySelect');
        if (!constituencySelect) return;

        fetch(`/api/constituencies/by-county?county=${encodeURIComponent(county)}`)
            .then(res => res.json())
            .then(data => {
                constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
                data.forEach(consti => {
                    const opt = new Option(consti, consti);
                    if (consti === currentConstituency) opt.selected = true;
                    constituencySelect.add(opt);
                });

                if (currentConstituency && document.getElementById('wardSelect')) {
                    loadWards(currentConstituency);
                }
            });
    }

    function loadWards(constituency) {
        const wardSelect = document.getElementById('wardSelect');
        if (!wardSelect) return;

        fetch(`/api/wards/by-constituency?constituency=${encodeURIComponent(constituency)}`)
            .then(res => res.json())
            .then(data => {
                wardSelect.innerHTML = '<option value="">Select Ward</option>';
                data.forEach(ward => {
                    const opt = new Option(ward, ward);
                    if (ward === currentWard) opt.selected = true;
                    wardSelect.add(opt);
                });
            });
    }

    // Initialize on load
    const initialPositionName = positionSelect.options[positionSelect.selectedIndex].text;
    loadJurisdictionFields(initialPositionName);

    // Listen for position change
    positionSelect.addEventListener('change', function() {
        const positionName = this.options[this.selectedIndex].text;
        loadJurisdictionFields(positionName);
    });
});
</script>
@endpush