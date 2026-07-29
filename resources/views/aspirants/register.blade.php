@extends('layouts.landing')

@section('title', 'Aspirant Registration - My Leader Kenya')

@section('content')
<div class="flag-stripe"></div>
@unless(request()->boolean('modal'))
@include('components.frontend-nav')
@endunless

<main class="min-h-screen bg-zinc-950 px-5 {{ request()->boolean('modal') ? 'py-8' : 'py-14' }} text-white">
    <div class="mx-auto max-w-4xl">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.24em] text-emerald-400">Aspirant Registration</p>
            <h1 class="mt-3 text-4xl font-semibold">Create your aspirant profile</h1>
            <p class="mt-3 max-w-2xl text-zinc-400">Submit your account and campaign profile for admin review.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('aspirants.register.store', request()->boolean('modal') ? ['modal' => 1] : []) }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border border-zinc-800 bg-zinc-900 p-8" id="aspirantRegisterForm">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Nick Name</label>
                    <input type="text" name="nick_name" value="{{ old('nick_name') }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Confirm Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Political Party</label>
                    <select name="political_party_id" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        <option value="">Select Political Party (Optional)</option>
                        @foreach($politicalParties as $party)
                            <option value="{{ $party->id }}" {{ old('political_party_id') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-zinc-400">Position <span class="text-red-400">*</span></label>
                    <select name="position_id" id="positionSelect" required class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                        <option value="">Select Position</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="jurisdictionFields" class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3"></div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-zinc-700 bg-zinc-800/70 p-4">
                    <label class="mb-3 block text-sm text-zinc-400">Profile Picture</label>
                    <div class="flex items-center gap-4">
                        <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-900 text-zinc-500" data-photo-preview="profile_picture">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <button type="button" class="rounded-xl bg-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-600" data-photo-trigger="profile_picture">
                                Add Photo
                            </button>
                            <p class="mt-2 text-xs text-zinc-500" data-photo-name="profile_picture">JPG, PNG, or WEBP.</p>
                        </div>
                    </div>
                    <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="hidden" data-photo-input="profile_picture">
                </div>
                <div class="rounded-2xl border border-zinc-700 bg-zinc-800/70 p-4">
                    <label class="mb-3 block text-sm text-zinc-400">Cover Photo</label>
                    <div class="overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-900">
                        <div class="grid aspect-[16/7] place-items-center text-zinc-500" data-photo-preview="cover_photo">
                            <div class="text-center">
                                <i class="fas fa-image text-2xl"></i>
                                <div class="mt-2 text-xs font-semibold uppercase tracking-wider">No cover selected</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="min-w-0 text-xs text-zinc-500" data-photo-name="cover_photo">Use a wide image for the public profile header.</p>
                        <button type="button" class="shrink-0 rounded-xl bg-zinc-700 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-600" data-photo-trigger="cover_photo">
                            Add Cover
                        </button>
                    </div>
                    <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" class="hidden" data-photo-input="cover_photo">
                </div>
            </div>

            <div class="mt-6">
                <label class="mb-2 block text-sm text-zinc-400">About You</label>
                <textarea name="about" rows="5" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">{{ old('about') }}</textarea>
            </div>

            <button type="submit" class="mt-8 w-full rounded-2xl bg-emerald-600 py-4 font-semibold hover:bg-emerald-700">Submit for Approval</button>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
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

document.querySelectorAll('[data-photo-trigger]').forEach(function (button) {
    button.addEventListener('click', function () {
        document.querySelector(`[data-photo-input="${button.dataset.photoTrigger}"]`)?.click();
    });
});

document.querySelectorAll('[data-photo-input]').forEach(function (input) {
    input.addEventListener('change', function () {
        const file = input.files?.[0];
        if (!file) return;

        const key = input.dataset.photoInput;
        const preview = document.querySelector(`[data-photo-preview="${key}"]`);
        const fileName = document.querySelector(`[data-photo-name="${key}"]`);
        const trigger = document.querySelector(`[data-photo-trigger="${key}"]`);
        const imageUrl = URL.createObjectURL(file);

        if (preview) {
            preview.innerHTML = `<img src="${imageUrl}" alt="Selected ${key.replace('_', ' ')} preview" class="h-full w-full object-cover">`;
        }

        if (fileName) {
            fileName.textContent = file.name;
        }

        if (trigger) {
            trigger.textContent = key === 'cover_photo' ? 'Change Cover' : 'Change Photo';
        }
    });
});
</script>
@endpush

