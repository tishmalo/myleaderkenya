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

            <div class="mb-8 border-b border-zinc-800">
                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Candidate edit sections">
                    <button type="button" data-candidate-tab-button="profile" class="candidate-tab-btn active px-5 py-3 rounded-t-2xl text-sm font-semibold border border-b-0 border-emerald-500 bg-emerald-600 text-white">
                        <i class="fas fa-user mr-2"></i> Profile
                    </button>
                    <button type="button" data-candidate-tab-button="tools" class="candidate-tab-btn px-5 py-3 rounded-t-2xl text-sm font-semibold border border-b-0 border-zinc-800 bg-zinc-950 text-zinc-400 hover:text-white">
                        <i class="fas fa-toolbox mr-2"></i> Tools
                    </button>
                    <button type="button" data-candidate-tab-button="priorities" class="candidate-tab-btn px-5 py-3 rounded-t-2xl text-sm font-semibold border border-b-0 border-zinc-800 bg-zinc-950 text-zinc-400 hover:text-white">
                        <i class="fas fa-bullseye mr-2"></i> Campaign Priorities
                    </button>
                    @if($candidate->parliamentMember)
                    <button type="button" data-candidate-tab-button="parliament" class="candidate-tab-btn px-5 py-3 rounded-t-2xl text-sm font-semibold border border-b-0 border-zinc-800 bg-zinc-950 text-zinc-400 hover:text-white">
                        <i class="fas fa-landmark-dome mr-2"></i> Parliamentary Data
                    </button>
                    @endif
                </div>
            </div>

            <section data-candidate-tab-panel="profile">
                <div class="mb-7 overflow-x-auto border-b border-zinc-800" role="tablist" aria-label="Candidate profile sections">
                    <div class="flex min-w-max gap-2">
                        <button type="button" data-profile-tab-button="basic" class="profile-tab-btn rounded-t-xl border border-b-0 border-emerald-500 bg-emerald-600 px-4 py-3 text-sm font-semibold text-white" aria-controls="profile-basic"><i class="fas fa-address-card mr-2"></i>Basic Information</button>
                        <button type="button" data-profile-tab-button="political" class="profile-tab-btn rounded-t-xl border border-b-0 border-zinc-800 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-400" aria-controls="profile-political"><i class="fas fa-landmark mr-2"></i>Political Aspirant</button>
                        <button type="button" data-profile-tab-button="social" class="profile-tab-btn rounded-t-xl border border-b-0 border-zinc-800 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-400" aria-controls="profile-social"><i class="fas fa-share-nodes mr-2"></i>Social Media</button>
                        <button type="button" data-profile-tab-button="media" class="profile-tab-btn rounded-t-xl border border-b-0 border-zinc-800 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-400" aria-controls="profile-media"><i class="fas fa-photo-film mr-2"></i>Media</button>
                        <button type="button" data-profile-tab-button="support" class="profile-tab-btn rounded-t-xl border border-b-0 border-zinc-800 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-400" aria-controls="profile-support"><i class="fas fa-people-group mr-2"></i>Support Groups</button>
                    </div>
                </div>

                <section id="profile-basic" data-profile-tab-panel="basic">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $candidate->name) }}" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Nick Name</label>
                            <input type="text" name="nick_name" value="{{ old('nick_name', $candidate->nick_name) }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $candidate->phone) }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $candidate->email) }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="mb-2 block text-sm text-zinc-400">About Candidate</label>
                        <textarea name="about" rows="6" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">{{ old('about', $candidate->about) }}</textarea>
                    </div>
                </section>

                <section id="profile-political" data-profile-tab-panel="political" class="hidden">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Political Party</label>
                            <select name="political_party_id" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                                <option value="">No Political Party</option>
                                @foreach($politicalParties as $party)
                                    <option value="{{ $party->id }}" {{ old('political_party_id', $candidate->political_party_id) == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Position <span class="text-red-500">*</span></label>
                            <select name="position_id" id="positionSelect" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}" {{ old('position_id', $candidate->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="jurisdictionFields" class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3"></div>
                </section>

                <section id="profile-social" data-profile-tab-panel="social" class="hidden">
                    @include('candidates.partials.social-links', ['candidate' => $candidate])
                </section>

                <section id="profile-media" data-profile-tab-panel="media" class="hidden">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Profile Picture</label>
                            @if($candidate->profile_picture)
                                <img src="{{ Storage::url($candidate->profile_picture) }}" alt="Profile" class="mb-3 h-28 w-28 rounded-2xl border border-zinc-700 object-cover">
                            @endif
                            <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                            <p class="mt-2 text-xs text-zinc-500">JPG, PNG, or WebP up to 2MB.</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-zinc-400">Cover Photo</label>
                            @if($candidate->cover_photo)
                                <img src="{{ Storage::url($candidate->cover_photo) }}" alt="Cover" class="mb-3 h-28 w-full rounded-2xl border border-zinc-700 object-cover">
                            @endif
                            <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                            <p class="mt-2 text-xs text-zinc-500">Leave blank to keep the current cover photo. JPG, PNG, or WebP up to 5MB.</p>
                        </div>
                    </div>
                </section>

                <section id="profile-support" data-profile-tab-panel="support" class="hidden">
                    @include('candidates.partials.support-contacts')
                </section>
            </section>
            <section data-candidate-tab-panel="priorities" class="hidden">
                <div class="rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div><h2 class="text-xl font-semibold text-white">Campaign manifesto priorities</h2><p class="mt-2 text-sm text-zinc-500">Aspirant submissions are read-only here. Approve or reject them from the central review queue.</p></div>
                        <a href="{{ route('campaign-priority-categories.index', ['candidate' => $candidate->name]) }}" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Open review queue</a>
                    </div>
                    <div class="mt-5 grid gap-3">
                        @forelse($candidate->campaignPriorities->sortBy(fn ($priority) => $priority->category?->sort_order ?? $priority->sort_order) as $priority)
                            <article class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4"><div class="flex items-center gap-3"><i class="{{ $priority->category?->icon }} text-emerald-400"></i><strong class="text-white">{{ $priority->category?->name ?: 'Retired category' }}</strong><span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase {{ $priority->status==='approved'?'bg-emerald-500/10 text-emerald-300':($priority->status==='rejected'?'bg-red-500/10 text-red-300':'bg-amber-500/10 text-amber-300') }}">{{ $priority->status }}</span></div><p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-400">{{ $priority->manifesto }}</p></article>
                        @empty
                            <p class="rounded-2xl border border-dashed border-zinc-800 p-7 text-center text-zinc-500">This aspirant has not submitted campaign priorities.</p>
                        @endforelse
                    </div>
                </div>
            </section>
            @if($candidate->parliamentMember)
            <section data-candidate-tab-panel="parliament" class="hidden">
                <div class="rounded-3xl border border-zinc-800 bg-zinc-950 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div><h2 class="text-xl font-semibold text-white">{{ $candidate->parliamentMember->source_name }}</h2><p class="mt-2 text-sm text-zinc-400">{{ collect([$candidate->parliamentMember->house, $candidate->parliamentMember->constituency, $candidate->parliamentMember->party])->filter()->implode(' / ') }}</p></div>
                        <a href="{{ route('parliament-members.index', ['search' => $candidate->parliamentMember->source_name]) }}" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold">Review Parliamentary Record</a>
                    </div>
                    <div class="mt-5 grid gap-3 md:grid-cols-3"><div class="rounded-2xl bg-zinc-900 p-4"><span class="text-xs uppercase text-zinc-500">Match</span><strong class="mt-1 block text-white">{{ ucfirst($candidate->parliamentMember->match_method ?: 'Unmatched') }}</strong></div><div class="rounded-2xl bg-zinc-900 p-4"><span class="text-xs uppercase text-zinc-500">Detail</span><strong class="mt-1 block text-white">{{ ucfirst($candidate->parliamentMember->detail_status) }}</strong></div><div class="rounded-2xl bg-zinc-900 p-4"><span class="text-xs uppercase text-zinc-500">Publication</span><strong class="mt-1 block {{ $candidate->parliamentMember->is_published ? 'text-emerald-400' : 'text-amber-400' }}">{{ $candidate->parliamentMember->is_published ? 'Published' : 'Not published' }}</strong></div></div>
                </div>
            </section>
            @endif
            <section data-candidate-tab-panel="tools" class="hidden">
            @php($smsSetting = \Illuminate\Support\Facades\Schema::hasTable('candidate_sms_settings') ? $candidate->smsSetting : null)
            <div class="border border-zinc-800 rounded-3xl p-6 bg-zinc-950">
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

            </section>

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
    const tabButtons = document.querySelectorAll('[data-candidate-tab-button]');
    const tabPanels = document.querySelectorAll('[data-candidate-tab-panel]');
    const profileTabButtons = document.querySelectorAll('[data-profile-tab-button]');
    const profileTabPanels = document.querySelectorAll('[data-profile-tab-panel]');
    const requestedTab = (window.location.hash || '').replace('#', '');
    const requestedProfileTab = requestedTab.startsWith('profile-') ? requestedTab.replace('profile-', '') : '';
    const validCandidateTabs = Array.from(tabButtons, (button) => button.dataset.candidateTabButton);
    const validProfileTabs = Array.from(profileTabButtons, (button) => button.dataset.profileTabButton);
    const defaultCandidateTab = '{{ $errors->hasAny(["sms_enabled", "sms_provider", "sms_base_url", "sms_sender_name", "sms_username", "sms_password"]) ? "tools" : "profile" }}';
    const defaultProfileTab = '{{ $errors->hasAny(["facebook_url", "x_url", "instagram_url", "tiktok_url", "youtube_url", "whatsapp_group_url"]) ? "social" : ($errors->hasAny(["profile_picture", "cover_photo"]) ? "media" : ($errors->hasAny(["position_id", "political_party_id", "country", "county", "constituency", "ward"]) ? "political" : ($errors->has('support_contacts.*') ? "support" : "basic"))) }}';
    const initialCandidateTab = requestedProfileTab ? 'profile' : (validCandidateTabs.includes(requestedTab) ? requestedTab : defaultCandidateTab);
    const initialProfileTab = validProfileTabs.includes(requestedProfileTab) ? requestedProfileTab : defaultProfileTab;

    function activateCandidateTab(tab) {
        tabButtons.forEach((button) => {
            const active = button.dataset.candidateTabButton === tab;
            button.classList.toggle('active', active);
            button.classList.toggle('bg-emerald-600', active);
            button.classList.toggle('border-emerald-500', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('bg-zinc-950', !active);
            button.classList.toggle('border-zinc-800', !active);
            button.classList.toggle('text-zinc-400', !active);
        });

        tabPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.candidateTabPanel !== tab);
        });
    }

    function activateProfileTab(tab) {
        profileTabButtons.forEach((button) => {
            const active = button.dataset.profileTabButton === tab;
            button.classList.toggle('bg-emerald-600', active);
            button.classList.toggle('border-emerald-500', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('bg-zinc-950', !active);
            button.classList.toggle('border-zinc-800', !active);
            button.classList.toggle('text-zinc-400', !active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        profileTabPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.profileTabPanel !== tab);
        });
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const tab = button.dataset.candidateTabButton;
            activateCandidateTab(tab);
            history.replaceState(null, '', '#' + tab);
        });
    });

    profileTabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const tab = button.dataset.profileTabButton;
            activateCandidateTab('profile');
            activateProfileTab(tab);
            history.replaceState(null, '', '#profile-' + tab);
        });
    });

    activateCandidateTab(initialCandidateTab);
    activateProfileTab(initialProfileTab);
    function initializeSupportContacts() {
        const panel = document.querySelector('[data-support-contacts-panel]');
        if (!panel) return;

        const list = panel.querySelector('[data-support-contact-list]');
        const template = document.querySelector('[data-support-contact-template]');
        const addButton = panel.querySelector('[data-add-support-contact]');

        function renumber() {
            panel.querySelectorAll('[data-support-contact-row]').forEach((row, index) => {
                row.querySelectorAll('[name^="support_contacts"], [data-name]').forEach((input) => {
                    const key = input.dataset.name || input.name.match(/\[([^\]]+)\]$/)?.[1];
                    if (key) input.name = `support_contacts[${index}][${key}]`;
                });
            });
        }

        addButton?.addEventListener('click', () => {
            if (!template || !list) return;
            list.appendChild(template.content.firstElementChild.cloneNode(true));
            renumber();
        });

        panel.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-support-contact]');
            if (!button) return;
            const row = button.closest('[data-support-contact-row]');
            row?.remove();
            renumber();
        });

        renumber();
    }

    initializeSupportContacts();

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
                    const name = optionName(county);
                    if (!name) return;
                    const opt = new Option(name, name);
                    if (name === currentCounty) opt.selected = true;
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

        if (constituencySelect) {
            constituencySelect.addEventListener('change', function() {
                loadWards(this.value);
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
                    const name = optionName(consti);
                    if (!name) return;
                    const opt = new Option(name, name);
                    if (name === currentConstituency) opt.selected = true;
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
                    const name = optionName(ward);
                    if (!name) return;
                    const opt = new Option(name, name);
                    if (name === currentWard) opt.selected = true;
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
