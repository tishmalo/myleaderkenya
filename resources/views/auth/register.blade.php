<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="nick_name" :value="__('Nick Name')" />
                <x-text-input id="nick_name" class="block mt-1 w-full" type="text" name="nick_name" :value="old('nick_name')" />
                <x-input-error :messages="$errors->get('nick_name')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="political_party_id" :value="__('Political Party')" />
            <select id="political_party_id" name="political_party_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Independent / No party</option>
                @foreach($politicalParties as $party)
                    <option value="{{ $party->id }}" @selected(old('political_party_id') == $party->id)>{{ $party->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('political_party_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="positionSelect" :value="__('Position')" />
            <select id="positionSelect" name="position_id" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Select Position</option>
                @foreach($positions as $position)
                    <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
        </div>

        <div id="jurisdictionFields" class="grid grid-cols-1 md:grid-cols-3 gap-4"></div>

        <div>
            <x-input-label for="about" :value="__('About Your Campaign')" />
            <textarea id="about" name="about" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('about') }}</textarea>
            <x-input-error :messages="$errors->get('about')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Submit for Approval') }}</x-primary-button>
        </div>
    </form>

    <script>
    const positionSelect = document.getElementById('positionSelect');
    const fields = document.getElementById('jurisdictionFields');
    let counties = [];
    const old = @json(old());
    const optionName = item => item && typeof item === 'object' ? (item.name || item.label || '') : item;
    const optionId = item => item && typeof item === 'object' ? (item.id || '') : '';
    async function fetchJson(url){ const r = await fetch(url); return await r.json(); }
    async function loadCounties(){ counties = await fetchJson('/api/counties'); }
    function selectHtml(name, id, label, required = false) { return `<div><label class="block font-medium text-sm text-gray-700">${label}${required ? ' *' : ''}</label><select name="${name}" id="${id}" ${required ? 'required' : ''} class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"><option value="">Select ${label}</option></select></div>`; }
    function fill(select, items, value){ select.innerHTML = `<option value="">Select ${select.name.replace('_',' ')}</option>`; items.forEach(item => { const opt = document.createElement('option'); opt.value = optionName(item); opt.dataset.id = optionId(item); opt.textContent = optionName(item); opt.selected = opt.value === value; select.appendChild(opt); }); }
    async function render(){
        const name = positionSelect.options[positionSelect.selectedIndex]?.text.toLowerCase() || '';
        if (!name) { fields.innerHTML = ''; return; }
        const president = name.includes('president'), countyOnly = name.includes('governor') || name.includes('senator') || name.includes('woman'), mp = name.includes('mp') || name.includes('member of parliament'), mca = name.includes('mca') || name.includes('county assembly');
        fields.innerHTML = president ? '<input type="hidden" name="country" value="Kenya">' : selectHtml('county','countySelect','County', countyOnly) + (mp || mca ? selectHtml('constituency','constituencySelect','Constituency', mp) : '') + (mca ? selectHtml('ward','wardSelect','Ward', true) : '');
        const county = document.getElementById('countySelect'), cons = document.getElementById('constituencySelect'), ward = document.getElementById('wardSelect');
        if (county) fill(county, counties, old.county || '');
        if (county && cons) county.addEventListener('change', async () => { fill(cons, await fetchJson('/api/constituencies?county_id=' + (county.selectedOptions[0]?.dataset.id || '')), old.constituency || ''); if (ward) ward.innerHTML = '<option value="">Select Ward</option>'; });
        if (cons && ward) cons.addEventListener('change', async () => fill(ward, await fetchJson('/api/wards?constituency_id=' + (cons.selectedOptions[0]?.dataset.id || '')), old.ward || ''));
    }
    positionSelect.addEventListener('change', render);
    loadCounties().then(render);
    </script>
</x-guest-layout>
