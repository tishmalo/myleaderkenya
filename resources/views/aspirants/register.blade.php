@extends('layouts.landing')

@section('title', ($selectedCandidate ? 'Claim Profile' : 'Submit Aspirant') . ' - My Leader Kenya')

@push('styles')
<style>
.aspirant-form-shell{background:radial-gradient(circle at top left,rgba(0,168,107,.09),transparent 30%),#09090b;color:#fff}.aspirant-form-wrap{max-width:980px;margin:auto}.aspirant-form-intro{margin-bottom:28px}.aspirant-kicker{color:#34d399;font-size:12px;font-weight:900;letter-spacing:.24em;text-transform:uppercase}.aspirant-form-intro h1{margin:10px 0 8px;font:700 clamp(32px,5vw,50px)/1.05 'Oswald',sans-serif}.aspirant-form-intro p{color:#a1a1aa}.aspirant-panel{margin-top:18px;border:1px solid #29292d;border-radius:24px;background:#151517;padding:clamp(20px,4vw,32px);box-shadow:0 22px 60px rgba(0,0,0,.24)}.aspirant-panel-head{display:flex;align-items:flex-start;gap:13px;margin-bottom:22px}.aspirant-panel-num{display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:rgba(16,185,129,.14);color:#34d399;font-weight:900}.aspirant-panel h2{font:700 21px 'Oswald',sans-serif}.aspirant-panel-head p{margin-top:3px;color:#71717a;font-size:13px}.aspirant-field-label{display:block;margin-bottom:8px;color:#d4d4d8;font-size:13px;font-weight:700}.aspirant-field,.aspirant-select,.aspirant-textarea{width:100%;border:1px solid #3f3f46;border-radius:13px;background:#242427;color:#fff;padding:12px 14px;outline:none}.aspirant-field:focus,.aspirant-select:focus,.aspirant-textarea:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.1)}.aspirant-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.aspirant-grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}.aspirant-role-switch{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;padding:6px;border:1px solid #303036;border-radius:15px;background:#0f0f11}.aspirant-adoption-tools{margin-top:18px;border:1px solid rgba(16,185,129,.28);border-radius:18px;background:rgba(16,185,129,.06);padding:18px}.aspirant-adoption-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}.aspirant-adoption-option{position:relative}.aspirant-adoption-option input{position:absolute;opacity:0}.aspirant-adoption-option span{display:flex;min-height:72px;align-items:center;gap:12px;border:1px solid #3f3f46;border-radius:14px;background:#202023;padding:14px;color:#d4d4d8;cursor:pointer}.aspirant-adoption-option input:checked+span{border-color:#10b981;background:rgba(16,185,129,.13);color:#a7f3d0;box-shadow:0 0 0 2px rgba(16,185,129,.12)}.aspirant-adoption-option i{color:#34d399}.aspirant-role-switch input{position:absolute;opacity:0}.aspirant-role-switch span{display:flex;align-items:center;justify-content:center;gap:8px;min-height:46px;border-radius:10px;color:#a1a1aa;font-weight:800;cursor:pointer}.aspirant-role-switch input:checked+span{background:#242427;color:#34d399;box-shadow:0 5px 18px rgba(0,0,0,.2)}.aspirant-privacy-note{display:flex;gap:10px;margin-top:14px;border-radius:12px;background:rgba(16,185,129,.08);padding:12px;color:#a7f3d0;font-size:12px;line-height:1.5}.aspirant-search{position:relative}.aspirant-search-control{display:flex;align-items:center;gap:10px;border:1px solid #3f3f46;border-radius:14px;background:#242427;padding:0 14px}.aspirant-search-control:focus-within{border-color:#10b981}.aspirant-search-control input{width:100%;height:50px;border:0;background:transparent;color:#fff;outline:0}.aspirant-search-spinner.is-active{width:15px;height:15px;border:2px solid #52525b;border-top-color:#34d399;border-radius:50%;animation:asp-spin .7s linear infinite}@keyframes asp-spin{to{transform:rotate(360deg)}}.aspirant-search-results{position:absolute;z-index:30;top:82px;left:0;right:0;max-height:330px;overflow:auto;border:1px solid #3f3f46;border-radius:14px;background:#18181b;padding:7px;box-shadow:0 22px 50px rgba(0,0,0,.55)}.aspirant-search-option{display:flex;width:100%;align-items:center;gap:12px;border:0;border-radius:10px;background:transparent;padding:10px;color:#fff;text-align:left}.aspirant-search-option:hover,.aspirant-search-option.is-active{background:#29292d}.aspirant-search-option-avatar,.aspirant-search-avatar{display:grid;flex:0 0 auto;place-items:center;width:45px;height:45px;overflow:hidden;border-radius:12px;background:#064e3b;color:#a7f3d0;font-weight:900}.aspirant-search-option-avatar img,.aspirant-search-avatar img{width:100%;height:100%;object-fit:cover}.aspirant-search-option-copy{display:grid;gap:3px}.aspirant-search-option-copy small,.aspirant-search-selected-copy span{color:#a1a1aa;font-size:12px}.aspirant-search-message{padding:24px;text-align:center;color:#a1a1aa}.aspirant-search-selection{align-items:center;gap:13px;border:1px solid rgba(16,185,129,.35);border-radius:15px;background:rgba(16,185,129,.07);padding:14px}.aspirant-search-selection:not([hidden]){display:flex}.aspirant-search-selected-copy{display:grid;min-width:0;flex:1;gap:3px}.aspirant-search-selected-copy small{color:#6ee7b7}.aspirant-search-selection button{border:1px solid #3f3f46;border-radius:9px;background:#242427;padding:8px 11px;color:#fff;font-size:12px;font-weight:800}.aspirant-search-help{margin-top:9px;color:#71717a;font-size:12px}.aspirant-email-status{min-height:18px;margin-top:7px;font-size:12px;font-weight:700}.aspirant-email-status.is-checking{color:#a1a1aa}.aspirant-email-status.is-available{color:#34d399}.aspirant-email-status.is-unavailable{color:#f87171}.aspirant-field[aria-invalid="true"]{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1)}.aspirant-submit{width:100%;margin-top:22px;border:0;border-radius:15px;background:#059669;padding:15px;color:#fff;font-weight:900}.aspirant-submit:hover{background:#047857}[hidden]{display:none!important}@media(max-width:720px){.aspirant-grid,.aspirant-grid-3,.aspirant-adoption-grid{grid-template-columns:1fr}.aspirant-role-switch{grid-template-columns:1fr}.aspirant-panel{border-radius:18px}.aspirant-search-selection{align-items:flex-start;flex-wrap:wrap}.aspirant-search-selection button{margin-left:58px}}
</style>
@endpush

@section('content')
<div class="flag-stripe"></div>
@unless(request()->boolean('modal'))
@include('components.frontend-nav')
@endunless

<main class="aspirant-form-shell min-h-screen px-5 {{ request()->boolean('modal') ? 'py-8' : 'py-14' }}">
<div class="aspirant-form-wrap">
    <header class="aspirant-form-intro">
        <div class="aspirant-kicker">{{ $selectedCandidate ? 'Profile Access Request' : 'Aspirant Registration' }}</div>
        <h1>{{ $selectedCandidate ? 'Claim this aspirant profile' : 'Submit an aspirant profile' }}</h1>
        <p>{{ $selectedCandidate ? 'Submit your account details for admin verification.' : 'Find an existing profile or submit a new one for admin verification.' }}</p>
    </header>

    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-100">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('aspirants.register.store', request()->boolean('modal') ? ['modal' => 1] : []) }}" method="POST" enctype="multipart/form-data" id="aspirantRegisterForm">
        @csrf
        <section class="aspirant-panel">
            <div class="aspirant-panel-head"><span class="aspirant-panel-num">1</span><div><h2>{{ $selectedCandidate ? 'Selected aspirant' : 'Find the aspirant' }}</h2><p>{{ $selectedCandidate ? 'This public profile is locked and cannot be changed from this request.' : 'Avoid duplicate profiles by searching first.' }}</p></div></div>
            <x-aspirant-search
                name="candidate_id"
                :search-url="route('aspirants.search')"
                :selected-candidate="$selectedCandidate"
                :locked="(bool) $selectedCandidate"
            />
        </section>

        <section class="aspirant-panel">
            <div class="aspirant-panel-head"><span class="aspirant-panel-num">2</span><div><h2>Who is submitting?</h2><p>{{ auth()->check() ? 'This determines your relationship to the aspirant.' : 'This determines whose login account and password will be created.' }}</p></div></div>
            <div class="aspirant-role-switch">
                <label><input type="radio" name="submission_mode" value="self" {{ old('submission_mode','self') === 'self' ? 'checked' : '' }}><span><i class="fas fa-user"></i> I am the aspirant</span></label>
                <label><input type="radio" name="submission_mode" value="representative" {{ old('submission_mode') === 'representative' ? 'checked' : '' }}><span><i class="fas fa-people-group"></i> I'm submitting on behalf</span></label>
                <label><input type="radio" name="submission_mode" value="adoption" {{ old('submission_mode') === 'adoption' ? 'checked' : '' }}><span><i class="fas fa-hand-holding-heart"></i> Adopt An Aspirant</span></label>
            </div>
            <div class="mt-4" data-relationship-wrap hidden>
                <label class="aspirant-field-label" for="relationship">I am the aspirant's</label>
                <select class="aspirant-select" id="relationship" name="relationship">
                    <option value="PA" {{ old('relationship') === 'PA' ? 'selected' : '' }}>Personal Assistant (PA)</option>
                    <option value="campaign_manager" {{ old('relationship') === 'campaign_manager' ? 'selected' : '' }}>Campaign Team</option>
                </select>
            </div>
            <div class="aspirant-adoption-tools" data-adoption-tools hidden>
                <h3 class="font-['Oswald'] text-xl font-semibold">What would you like to sponsor?</h3>
                <p class="mt-1 text-sm text-zinc-400">Choose as many tools as you want. Package and price selection happens later when you fund each tool from My Toolbox.</p>
                <div class="aspirant-adoption-grid">
                    @forelse($adoptableTools as $tool)
                        @php($isBulkSms = str_contains(strtolower($tool->slug.' '.$tool->title), 'bulk-sms') || str_contains(strtolower($tool->title), 'bulk sms'))
                        <div class="aspirant-adoption-option">
                        <label>
                            <input type="checkbox" name="adoption_tool_ids[]" value="{{ $tool->id }}" {{ in_array($tool->id, array_map('intval', old('adoption_tool_ids', [])), true) ? 'checked' : '' }}>
                            <span><i class="fas fa-circle-check"></i><strong>{{ $tool->title }}</strong><small class="ml-auto text-zinc-400">{{ $isBulkSms ? 'SMS tokens' : 'Token package' }}</small></span>
                        </label>
                        </div>
                    @empty
                        <p class="text-sm text-amber-300">No sponsorship tools are currently available. Please check again later.</p>
                    @endforelse
                </div>
            </div>

            <div class="aspirant-privacy-note"><i class="fas fa-shield-halved"></i><span>{{ auth()->check() ? 'This request will use your current signed-in account. Existing aspirant contact data is never displayed or prefilled.' : 'Your password always belongs to you - the person submitting this form. Existing aspirant email and phone data are never displayed or prefilled.' }}</span></div>
        </section>

        <div data-new-profile>
            <section class="aspirant-panel">
                <div class="aspirant-panel-head"><span class="aspirant-panel-num">3</span><div><h2>Aspirant profile</h2><p>Public campaign information and optional contact details for a new profile.</p></div></div>
                <div class="aspirant-grid">
                    <div><label class="aspirant-field-label">Aspirant Name <b class="text-red-400">*</b></label><input class="aspirant-field" type="text" name="aspirant_name" value="{{ old('aspirant_name') }}"></div>
                    <div><label class="aspirant-field-label">Aspirant Nickname</label><input class="aspirant-field" type="text" name="nick_name" value="{{ old('nick_name') }}"></div>
                    <div><label class="aspirant-field-label">Aspirant Email <span data-self-required class="text-red-400">*</span></label><input class="aspirant-field" type="email" name="aspirant_email" value="{{ old('aspirant_email') }}" autocomplete="email" data-email-availability aria-describedby="aspirantEmailStatus"><p class="aspirant-email-status" id="aspirantEmailStatus" data-email-status aria-live="polite"></p></div>
                    <div><label class="aspirant-field-label">Aspirant Phone</label><input class="aspirant-field" type="tel" name="aspirant_phone" autocomplete="tel"></div>
                    <div><label class="aspirant-field-label">Political Party</label><select class="aspirant-select" name="political_party_id"><option value="">Select Political Party (Optional)</option>@foreach($politicalParties as $party)<option value="{{ $party->id }}" {{ old('political_party_id') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>@endforeach</select></div>
                    <div><label class="aspirant-field-label">Position <b class="text-red-400">*</b></label><select class="aspirant-select" name="position_id" id="positionSelect"><option value="">Select Position</option>@foreach($positions as $pos)<option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>@endforeach</select></div>
                </div>
                <div id="jurisdictionFields" class="aspirant-grid aspirant-grid-3 mt-5"></div>
                <div class="aspirant-grid mt-5">
                    <div class="rounded-2xl border border-zinc-700 bg-zinc-800/70 p-4"><label class="aspirant-field-label">Aspirant Profile Picture</label><div class="flex items-center gap-4"><div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-900 text-zinc-500" data-photo-preview="profile_picture"><i class="fas fa-user text-2xl"></i></div><div><label for="profilePictureInput" class="inline-flex cursor-pointer rounded-xl bg-zinc-700 px-4 py-2 text-sm font-semibold" data-photo-trigger="profile_picture">Add Photo</label><p class="mt-2 text-xs text-zinc-500" data-photo-name="profile_picture">JPG, PNG, or WEBP.</p></div></div><input type="file" id="profilePictureInput" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="hidden" data-photo-input="profile_picture"></div>
                    <div class="rounded-2xl border border-zinc-700 bg-zinc-800/70 p-4"><label class="aspirant-field-label">Aspirant Cover Photo</label><div class="overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-900"><div class="grid aspect-[16/7] place-items-center text-zinc-500" data-photo-preview="cover_photo"><i class="fas fa-image text-2xl"></i></div></div><div class="mt-3 flex justify-between gap-3"><p class="text-xs text-zinc-500" data-photo-name="cover_photo">Use a wide image.</p><label for="coverPhotoInput" class="cursor-pointer rounded-xl bg-zinc-700 px-4 py-2 text-sm font-semibold" data-photo-trigger="cover_photo">Add Cover</label></div><input type="file" id="coverPhotoInput" name="cover_photo" accept="image/jpeg,image/png,image/webp" class="hidden" data-photo-input="cover_photo"></div>
                </div>
                <div class="mt-5"><label class="aspirant-field-label">About the Aspirant</label><textarea class="aspirant-textarea" name="about" rows="5">{{ old('about') }}</textarea></div>
            </section>
        </div>

        @guest
        <section class="aspirant-panel">
            <div class="aspirant-panel-head"><span class="aspirant-panel-num" data-account-step>4</span><div><h2>Your secure account</h2><p data-account-copy>The password below is yours and is never the aspirant's when you are a PA or campaign-team member.</p></div></div>
            <div class="aspirant-grid" data-account-identity hidden>
                <div><label class="aspirant-field-label">Your Full Name <b class="text-red-400">*</b></label><input class="aspirant-field" type="text" name="account_name" value="{{ old('account_name') }}" autocomplete="name"></div>
                <div><label class="aspirant-field-label">Your Email <b class="text-red-400">*</b></label><input class="aspirant-field" type="email" name="account_email" value="{{ old('account_email') }}" autocomplete="email" data-email-availability aria-describedby="accountEmailStatus"><p class="aspirant-email-status" id="accountEmailStatus" data-email-status aria-live="polite"></p></div>
                <div><label class="aspirant-field-label">Your Phone</label><input class="aspirant-field" type="tel" name="account_phone" autocomplete="tel"></div>
            </div>
            <div class="aspirant-grid mt-5">
                <div><label class="aspirant-field-label">Your Password <b class="text-red-400">*</b></label><input class="aspirant-field" type="password" name="password" required autocomplete="new-password"></div>
                <div><label class="aspirant-field-label">Confirm Your Password <b class="text-red-400">*</b></label><input class="aspirant-field" type="password" name="password_confirmation" required autocomplete="new-password"></div>
            </div>
            <button class="aspirant-submit" type="submit">Submit for Admin Verification</button>
        </section>
        @else
        <section class="aspirant-panel">
            <div class="aspirant-privacy-note"><i class="fas fa-circle-check"></i><span><strong>{{ auth()->user()->name }}</strong><br>{{ auth()->user()->email }}</span></div>
            <button class="aspirant-submit" type="submit">Submit for Admin Verification</button>
        </section>
        @endguest
    </form>
</div>
</main>
<div class="fixed inset-0 z-[30000] hidden items-center justify-center bg-black/80 p-5 backdrop-blur-sm" data-crop-modal aria-hidden="true">
    <div class="w-full max-w-2xl rounded-3xl border border-zinc-700 bg-zinc-900 p-6 text-white shadow-2xl">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h2 class="font-['Oswald'] text-2xl font-semibold" data-crop-title>Crop Photo</h2>
                <p class="mt-1 text-sm text-zinc-400">Drag the image to position it, then adjust zoom.</p>
            </div>
            <button type="button" class="grid h-10 w-10 place-items-center rounded-xl border border-zinc-700 bg-zinc-800 text-white hover:bg-zinc-700" data-crop-cancel aria-label="Close crop editor">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mx-auto max-h-[58vh] w-full touch-none overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-950" data-crop-frame>
            <img src="" alt="Crop preview" class="max-w-none select-none" data-crop-image draggable="false">
        </div>

        <label class="mt-5 block text-sm text-zinc-400">
            Zoom
            <input type="range" min="1" max="3" step="0.01" value="1" class="mt-2 w-full accent-emerald-500" data-crop-zoom>
        </label>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="rounded-xl border border-zinc-700 px-5 py-3 font-semibold text-zinc-200 hover:bg-zinc-800" data-crop-cancel>Cancel</button>
            <button type="button" class="rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-700" data-crop-apply>Use Cropped Photo</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const registrationForm = document.getElementById('aspirantRegisterForm');
const newProfileSection = document.querySelector('[data-new-profile]');
const accountIdentity = document.querySelector('[data-account-identity]');
const relationshipWrap = document.querySelector('[data-relationship-wrap]');
const adoptionTools = document.querySelector('[data-adoption-tools]');
const candidateValue = document.querySelector('[data-aspirant-search-value]');
const modeInputs = document.querySelectorAll('input[name="submission_mode"]');

function syncRegistrationMode() {
    const mode = document.querySelector('input[name="submission_mode"]:checked')?.value || 'self';
    const hasExistingCandidate = Boolean(candidateValue?.value);
    const representative = mode === 'representative';
    const adoption = mode === 'adoption';
    const accountHolder = representative || adoption;

    relationshipWrap.hidden = !representative;
    adoptionTools.hidden = !adoption;
    newProfileSection.hidden = hasExistingCandidate;
    if (accountIdentity) accountIdentity.hidden = !accountHolder && !hasExistingCandidate;
    const accountStep = document.querySelector('[data-account-step]');
    if (accountStep) accountStep.textContent = hasExistingCandidate ? '3' : '4';

    document.querySelectorAll('[data-new-profile] input, [data-new-profile] select, [data-new-profile] textarea').forEach(function (field) {
        field.disabled = hasExistingCandidate;
    });
    document.querySelectorAll('[data-account-identity] input').forEach(function (field) {
        field.disabled = accountIdentity ? accountIdentity.hidden : true;
    });
    document.querySelectorAll('[data-adoption-tools] input').forEach(function (field) {
        field.disabled = !adoption;
    });
}

modeInputs.forEach(function (input) { input.addEventListener('change', syncRegistrationMode); });
document.addEventListener('aspirant:selected', syncRegistrationMode);
document.addEventListener('aspirant:cleared', syncRegistrationMode);
syncRegistrationMode();

const emailAvailabilityUrl = @json(route('aspirants.email-availability'));
const emailCheckTimers = new WeakMap();
const emailCheckRequests = new WeakMap();

document.querySelectorAll('[data-email-availability]').forEach(function (field) {
    const status = field.parentElement.querySelector('[data-email-status]');

    function showEmailStatus(message, state) {
        status.textContent = message;
        status.className = 'aspirant-email-status' + (state ? ' is-' + state : '');
    }

    field.addEventListener('input', function () {
        clearTimeout(emailCheckTimers.get(field));
        emailCheckRequests.get(field)?.abort();
        field.setCustomValidity('');
        field.removeAttribute('aria-invalid');

        const email = field.value.trim().toLowerCase();
        if (!email || !field.validity.valid) {
            showEmailStatus('', '');
            return;
        }

        showEmailStatus('Checking email...', 'checking');
        emailCheckTimers.set(field, setTimeout(async function () {
            const controller = new AbortController();
            emailCheckRequests.set(field, controller);

            try {
                const response = await fetch(emailAvailabilityUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': registrationForm.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ email: email }),
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Email check failed');

                const result = await response.json();
                if (field.value.trim().toLowerCase() !== email) return;

                if (result.available) {
                    showEmailStatus(result.message, 'available');
                } else {
                    field.setCustomValidity(result.message);
                    field.setAttribute('aria-invalid', 'true');
                    showEmailStatus(result.message, 'unavailable');
                }
            } catch (error) {
                if (error.name !== 'AbortError') showEmailStatus('', '');
            }
        }, 450));
    });
});

const positionSelect = document.getElementById('positionSelect');
const jurisdictionFields = document.getElementById('jurisdictionFields');
let allCounties = [];
const oldValues = @json(['county' => old('county'), 'constituency' => old('constituency'), 'ward' => old('ward')]);
function optionName(item) { return typeof item === 'object' && item !== null ? (item.name || item.label || '') : item; }
function optionId(item) { return typeof item === 'object' && item !== null ? (item.id || '') : ''; }
async function fetchCounties() { try { const res = await fetch('/api/counties'); allCounties = await res.json(); } catch (e) {} }
async function fetchConstituencies(countyId) { if (!countyId) return []; const res = await fetch(`/api/constituencies?county_id=${countyId}`); return await res.json(); }
async function fetchWards(constituencyId) { if (!constituencyId) return []; const res = await fetch(`/api/wards?constituency_id=${constituencyId}`); return await res.json(); }
function selectHtml(name, id, label, required) { return `<div><label class="mb-2 block text-sm text-zinc-400">${label}${required ? ' <span class="text-red-400">*</span>' : ''}</label><select name="${name}" id="${id}" ${required ? 'required' : ''} class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Select ${label}</option></select></div>`; }
function renderJurisdictionFields(positionName) {
    const name = positionName.toLowerCase().trim();
    const isPresident = name.includes('president');
    const isCounty = name.includes('governor') || name.includes('senator') || name.includes('women representative');
    const isMP = name.includes('mp') || name.includes('member of parliament');
    const isMCA = name.includes('mca') || name.includes('county assembly');
    if (isPresident) jurisdictionFields.innerHTML = `<div class="md:col-span-3"><label class="mb-2 block text-sm text-zinc-400">Country</label><input type="text" name="country" value="Kenya" readonly class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"></div>`;
    else if (isCounty) jurisdictionFields.innerHTML = `<div class="md:col-span-3">${selectHtml('county','countySelect','County',true)}</div>`;
    else if (isMP) jurisdictionFields.innerHTML = selectHtml('county','countySelect','County',true) + `<div class="md:col-span-2">${selectHtml('constituency','constituencySelect','Constituency',true)}</div>`;
    else if (isMCA) jurisdictionFields.innerHTML = selectHtml('county','countySelect','County',true) + selectHtml('constituency','constituencySelect','Constituency',true) + selectHtml('ward','wardSelect','Ward',true);
    else jurisdictionFields.innerHTML = selectHtml('county','countySelect','County',false) + selectHtml('constituency','constituencySelect','Constituency',false) + selectHtml('ward','wardSelect','Ward',false);
    attachEventListeners();
}
function fillSelect(select, items, selectedValue) { items.forEach(item => { const opt = document.createElement('option'); const name = optionName(item); if (!name) return; opt.value = name; opt.dataset.id = optionId(item); opt.textContent = name; if (selectedValue === name) opt.selected = true; select.appendChild(opt); }); }
function selectedOptionId(select) { return select?.selectedOptions?.[0]?.dataset?.id || ''; }

async function loadConstituenciesForCounty(countySelect, constituencySelect, wardSelect) {
    const countyId = selectedOptionId(countySelect);
    if (!constituencySelect) return;
    constituencySelect.innerHTML = '<option value="">Loading...</option>';
    const data = await fetchConstituencies(countyId);
    constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
    fillSelect(constituencySelect, data, oldValues.constituency);
    if (wardSelect) wardSelect.innerHTML = '<option value="">Select Ward</option>';
    if (oldValues.constituency && wardSelect) {
        await loadWardsForConstituency(constituencySelect, wardSelect);
    }
}
async function loadWardsForConstituency(constituencySelect, wardSelect) {
    const consId = selectedOptionId(constituencySelect);
    if (!wardSelect) return;
    wardSelect.innerHTML = '<option value="">Loading...</option>';
    const data = await fetchWards(consId);
    wardSelect.innerHTML = '<option value="">Select Ward</option>';
    fillSelect(wardSelect, data, oldValues.ward);
}

function attachEventListeners() {
    const countySelect = document.getElementById('countySelect');
    const constituencySelect = document.getElementById('constituencySelect');
    const wardSelect = document.getElementById('wardSelect');
    if (countySelect) {
        fillSelect(countySelect, allCounties, oldValues.county);
        if (oldValues.county && constituencySelect) {
            loadConstituenciesForCounty(countySelect, constituencySelect, wardSelect);
        }
        countySelect.addEventListener('change', async function() {
            oldValues.constituency = '';
            oldValues.ward = '';
            await loadConstituenciesForCounty(this, constituencySelect, wardSelect);
        });
    }
    if (constituencySelect) {
        constituencySelect.addEventListener('change', async function() {
            oldValues.ward = '';
            await loadWardsForConstituency(this, wardSelect);
        });
    }
}
positionSelect.addEventListener('change', function() { renderJurisdictionFields(this.options[this.selectedIndex].text); });
fetchCounties().then(function () { if (positionSelect.value) positionSelect.dispatchEvent(new Event('change')); });

document.querySelectorAll('[data-photo-input]').forEach(function (input) {
    input.addEventListener('change', function () {
        const file = input.files?.[0];
        if (!file) return;

        openCropper(input, file, input.dataset.photoInput);
    });
});

const cropModal = document.querySelector('[data-crop-modal]');
const cropFrame = document.querySelector('[data-crop-frame]');
const cropImage = document.querySelector('[data-crop-image]');
const cropZoom = document.querySelector('[data-crop-zoom]');
const cropTitle = document.querySelector('[data-crop-title]');
const cropApply = document.querySelector('[data-crop-apply]');
const cropCancelButtons = document.querySelectorAll('[data-crop-cancel]');
let cropState = null;

function openCropper(input, file, key) {
    const reader = new FileReader();

    reader.onload = function (event) {
        const image = new Image();

        image.onload = function () {
            cropState = {
                input,
                file,
                key,
                image,
                x: 0,
                y: 0,
                dragging: false,
                dragX: 0,
                dragY: 0,
                imageX: 0,
                imageY: 0,
                baseScale: 1,
            };

            cropTitle.textContent = key === 'cover_photo' ? 'Crop Cover Photo' : 'Crop Profile Picture';
            cropFrame.style.aspectRatio = key === 'cover_photo' ? '16 / 7' : '1 / 1';
            cropImage.src = event.target.result;
            cropZoom.value = '1';
            cropModal.classList.remove('hidden');
            cropModal.classList.add('flex');
            cropModal.setAttribute('aria-hidden', 'false');

            requestAnimationFrame(function () {
                resetCropPosition();
                renderCropImage();
            });
        };

        image.src = event.target.result;
    };

    reader.readAsDataURL(file);
}

function resetCropPosition() {
    if (!cropState || !cropFrame) return;

    const frame = cropFrame.getBoundingClientRect();
    cropState.baseScale = Math.max(frame.width / cropState.image.width, frame.height / cropState.image.height);
    cropState.x = (frame.width - cropState.image.width * cropState.baseScale) / 2;
    cropState.y = (frame.height - cropState.image.height * cropState.baseScale) / 2;
}

function renderCropImage() {
    if (!cropState || !cropFrame || !cropImage) return;

    const frame = cropFrame.getBoundingClientRect();
    const scale = cropState.baseScale * Number(cropZoom.value || 1);
    const width = cropState.image.width * scale;
    const height = cropState.image.height * scale;

    cropState.x = Math.min(0, Math.max(frame.width - width, cropState.x));
    cropState.y = Math.min(0, Math.max(frame.height - height, cropState.y));

    cropImage.style.width = width + 'px';
    cropImage.style.height = height + 'px';
    cropImage.style.transform = `translate(${cropState.x}px, ${cropState.y}px)`;
    cropImage.style.transformOrigin = 'top left';
}

cropZoom?.addEventListener('input', renderCropImage);

cropFrame?.addEventListener('pointerdown', function (event) {
    if (!cropState) return;

    cropState.dragging = true;
    cropState.dragX = event.clientX;
    cropState.dragY = event.clientY;
    cropState.imageX = cropState.x;
    cropState.imageY = cropState.y;
    cropFrame.setPointerCapture(event.pointerId);
});

cropFrame?.addEventListener('pointermove', function (event) {
    if (!cropState?.dragging) return;

    cropState.x = cropState.imageX + event.clientX - cropState.dragX;
    cropState.y = cropState.imageY + event.clientY - cropState.dragY;
    renderCropImage();
});

cropFrame?.addEventListener('pointerup', function () {
    if (cropState) cropState.dragging = false;
});

function closeCropper(clearInput) {
    if (clearInput && cropState?.input) {
        cropState.input.value = '';
    }

    cropModal.classList.add('hidden');
    cropModal.classList.remove('flex');
    cropModal.setAttribute('aria-hidden', 'true');
    cropImage.src = '';
    cropState = null;
}

cropCancelButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        closeCropper(true);
    });
});

cropApply?.addEventListener('click', function () {
    if (!cropState || !cropFrame) return;

    const frame = cropFrame.getBoundingClientRect();
    const scale = cropState.baseScale * Number(cropZoom.value || 1);
    const aspect = cropState.key === 'cover_photo' ? 16 / 7 : 1;
    const outputWidth = cropState.key === 'cover_photo' ? 1600 : 900;
    const outputHeight = Math.round(outputWidth / aspect);
    const sourceX = Math.max(0, -cropState.x / scale);
    const sourceY = Math.max(0, -cropState.y / scale);
    const sourceWidth = Math.min(cropState.image.width - sourceX, frame.width / scale);
    const sourceHeight = Math.min(cropState.image.height - sourceY, frame.height / scale);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    canvas.width = outputWidth;
    canvas.height = outputHeight;
    context.drawImage(cropState.image, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, outputWidth, outputHeight);

    canvas.toBlob(function (blob) {
        if (!blob || !cropState) return;

        const croppedFile = new File([blob], cropState.file.name.replace(/\.[^.]+$/, '') + '-cropped.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        const imageUrl = URL.createObjectURL(blob);

        dataTransfer.items.add(croppedFile);
        cropState.input.files = dataTransfer.files;
        updatePhotoPreview(cropState.key, croppedFile, imageUrl);
        closeCropper(false);
    }, 'image/jpeg', 0.9);
});

function updatePhotoPreview(key, file, imageUrl) {
    const preview = document.querySelector(`[data-photo-preview="${key}"]`);
    const fileName = document.querySelector(`[data-photo-name="${key}"]`);
    const trigger = document.querySelector(`[data-photo-trigger="${key}"]`);

    if (preview) {
        preview.innerHTML = `<img src="${imageUrl}" alt="Selected ${key.replace('_', ' ')} preview" class="h-full w-full object-cover">`;
    }

    if (fileName) {
        fileName.textContent = file.name;
    }

    if (trigger) {
        trigger.textContent = key === 'cover_photo' ? 'Change Cover' : 'Change Photo';
    }
}
</script>
@endpush
