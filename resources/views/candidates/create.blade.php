@extends('layouts.app')

@section('page_title', 'Add New Candidate')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-white">Add New Candidate</h1>
        <a href="{{ route('candidates.index') }}" class="text-zinc-400 hover:text-white">← Back</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data" id="candidateForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Nick Name</label>
                    <input type="text" name="nick_name" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone</label>
                    <input type="tel" name="phone" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email</label>
                    <input type="email" name="email" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <!-- Political Party -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Political Party</label>
                <select name="political_party_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Political Party (Optional)</option>
                    @foreach($politicalParties as $party)
                        <option value="{{ $party->id }}" {{ old('political_party_id') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Position -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Position <span class="text-red-500">*</span></label>
                <select name="position_id" id="positionSelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Position</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dynamic Jurisdiction Fields -->
            <div id="jurisdictionFields" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6"></div>

            <!-- Profile Picture -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Profile Picture</label>
                <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>


            <!-- Cover Photo -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Cover Photo</label>
                <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                <p class="mt-2 text-xs text-zinc-500">Wide banner image for the public aspirant profile. JPG, PNG, or WebP up to 5MB.</p>
            </div>
            <!-- About -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">About Candidate</label>
                <textarea name="about" rows="5" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></textarea>
            </div>

            @include('candidates.partials.support-contacts')

            <div class="mt-10">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Save Candidate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const positionSelect = document.getElementById('positionSelect');
const jurisdictionFields = document.getElementById('jurisdictionFields');

let allCounties = [];

function optionName(item) {
    return typeof item === 'object' && item !== null ? (item.name || item.label || '') : item;
}

function optionId(item) {
    return typeof item === 'object' && item !== null ? (item.id || '') : '';
}

// Fetch all counties once
async function fetchCounties() {
    try {
        const res = await fetch('/api/counties');
        allCounties = await res.json();
    } catch (e) {
        console.error('Failed to load counties', e);
    }
}

// Fetch constituencies by county
async function fetchConstituencies(countyId) {
    if (!countyId) return [];
    const res = await fetch(`/api/constituencies?county_id=${countyId}`);
    return await res.json();
}

// Fetch wards by constituency
async function fetchWards(constituencyId) {
    if (!constituencyId) return [];
    const res = await fetch(`/api/wards?constituency_id=${constituencyId}`);
    return await res.json();
}

function renderJurisdictionFields(positionName) {
    let html = '';

    const isPresident = positionName.includes('president');
    const isGovernor = positionName.includes('governor') || positionName.includes('senator') || positionName.includes('women representative');
    const isMP = positionName.includes('mp') || positionName.includes('member of parliament');
    const isMCA = positionName.includes('mca') || positionName.includes('county assembly');

    if (isPresident) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">Country</label>
                <input type="text" name="country" value="Kenya" readonly 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>`;
    } 
    else if (isGovernor) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">County <span class="text-red-500">*</span></label>
                <select name="county" id="countySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>`;
    } 
    else if (isMP) {
        html = `
        
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2">Constituency <span class="text-red-500">*</span></label>
                <select name="constituency" id="constituencySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>`;
    } 
    else if (isMCA) {
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward <span class="text-red-500">*</span></label>
                <select name="ward" id="wardSelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Ward</option>
                </select>
            </div>`;
    } 
    else {
        // Default fallback
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward</label>
                <select name="ward" id="wardSelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>`;
    }

    jurisdictionFields.innerHTML = html;
    attachEventListeners();
}

function attachEventListeners() {
    const countySelect = document.getElementById('countySelect');
    const constituencySelect = document.getElementById('constituencySelect');
    const wardSelect = document.getElementById('wardSelect');

    if (countySelect) {
        // Populate counties
        allCounties.forEach(county => {
            const name = optionName(county);
            const opt = document.createElement('option');
            opt.value = name;
            opt.dataset.id = optionId(county);
            opt.textContent = name;
            countySelect.appendChild(opt);
        });

        countySelect.addEventListener('change', async function() {
            const countyId = this.selectedOptions[0]?.dataset.id || '';
            if (!constituencySelect) return;

            constituencySelect.innerHTML = '<option value="">Loading...</option>';
            const data = await fetchConstituencies(countyId);

            constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
            data.forEach(c => {
                const name = optionName(c);
                const opt = document.createElement('option');
                opt.value = name;
                opt.dataset.id = optionId(c);
                opt.textContent = name;
                constituencySelect.appendChild(opt);
            });

            // Reset ward if exists
            if (wardSelect) wardSelect.innerHTML = '<option value="">Select Ward</option>';
        });
    }

    if (constituencySelect) {
        constituencySelect.addEventListener('change', async function() {
            const consId = this.selectedOptions[0]?.dataset.id || '';
            if (!wardSelect) return;

            wardSelect.innerHTML = '<option value="">Loading...</option>';
            const data = await fetchWards(consId);

            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            data.forEach(w => {
                const name = optionName(w);
                const opt = document.createElement('option');
                opt.value = name;
                opt.dataset.id = optionId(w);
                opt.textContent = name;
                wardSelect.appendChild(opt);
            });
        });
    }
}

// Main listener
positionSelect.addEventListener('change', function() {
    const positionName = this.options[this.selectedIndex].text.toLowerCase().trim();
    renderJurisdictionFields(positionName);
});

// Initialize
fetchCounties();
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
</script>
@endpush

