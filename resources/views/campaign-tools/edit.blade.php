@extends('layouts.app')

@section('page_title', 'Edit Campaign Tool')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Edit Campaign Tool</h1>
        <a href="{{ route('campaign-tools.index') }}" class="text-zinc-400 hover:text-white">Back to Campaign Tools</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('campaign-tools.update', $campaignTool) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('campaign-tools._form', ['campaignTool' => $campaignTool])

            <div class="mt-10 flex gap-4">
                <a href="{{ route('campaign-tools.index') }}" class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Update Campaign Tool
                </button>
            </div>
        </form>
    </div>

    @unless(str_contains(strtolower($campaignTool->slug.' '.$campaignTool->title), 'bulk-sms'))
    <section class="mt-8 bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <h2 class="text-2xl font-semibold text-white">Paid packages</h2>
        <p class="mt-2 text-zinc-400">Set the Toolbox token cost and entitlement for each package. Bulk SMS continues to use direct token sponsorship instead.</p>
        @if($errors->any())
            <div class="mt-5 rounded-2xl border border-red-500/40 bg-red-950/40 px-5 py-4 text-red-200">
                <p class="font-semibold">The package was not saved:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="mt-6 grid gap-4">
            @foreach($campaignTool->packages as $package)
            <form method="POST" action="{{ route('campaign-tools.packages.update', [$campaignTool, $package]) }}" class="grid md:grid-cols-4 gap-3 border border-zinc-800 rounded-2xl p-4" data-package-form>@csrf @method('PUT')
                <input name="name" value="{{ $package->name }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Package name">
                <input type="number" min="1" name="token_cost" value="{{ $package->token_cost }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Token cost">
                <label class="text-sm text-zinc-400">Entitlement type<select name="entitlement_type" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white">@foreach(\App\Models\CampaignToolPackage::ENTITLEMENT_TYPES as $type)<option value="{{ $type }}" @selected($package->entitlement_type===$type)>{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select></label>
                <label class="text-sm text-zinc-400" data-quantity-field>Usage allowance<input type="number" min="1" name="entitlement_quantity" value="{{ $package->entitlement_quantity }}" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="e.g. 10 uses"></label>
                <label class="text-sm text-zinc-400" data-duration-field>Duration in days<input type="number" min="1" name="duration_days" value="{{ $package->duration_days }}" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="e.g. 30 days"></label>
                <input type="number" min="0" name="sort_order" value="{{ $package->sort_order }}" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Sort">
                <label class="flex items-center gap-2 text-zinc-300"><input type="checkbox" name="is_active" value="1" @checked($package->is_active)> Active</label>
                <textarea name="description" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Description">{{ $package->description }}</textarea>
                <textarea name="fulfilment_instructions" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Fulfilment instructions">{{ $package->fulfilment_instructions }}</textarea>
                <button class="bg-emerald-600 rounded-xl px-4 py-2 font-semibold">Save package</button>
            </form>
            @endforeach
            <form method="POST" action="{{ route('campaign-tools.packages.store', $campaignTool) }}" class="grid md:grid-cols-4 gap-3 border border-emerald-500/30 rounded-2xl p-4" data-package-form>@csrf
                <input name="name" value="{{ old('name') }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="New package name">
                <input type="number" min="1" name="token_cost" value="{{ old('token_cost') }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Token cost">
                <label class="text-sm text-zinc-400">Entitlement type<select name="entitlement_type" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white">@foreach(\App\Models\CampaignToolPackage::ENTITLEMENT_TYPES as $type)<option value="{{ $type }}" @selected(old('entitlement_type')===$type)>{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select></label>
                <label class="text-sm text-zinc-400" data-quantity-field>Usage allowance<input type="number" min="1" name="entitlement_quantity" value="{{ old('entitlement_quantity') }}" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="e.g. 10 uses"></label>
                <label class="text-sm text-zinc-400" data-duration-field>Duration in days<input type="number" min="1" name="duration_days" value="{{ old('duration_days') }}" class="mt-1 w-full bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="e.g. 30 days"></label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Sort">
                <label class="flex items-center gap-2 text-zinc-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label>
                <textarea name="description" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Description">{{ old('description') }}</textarea>
                <textarea name="fulfilment_instructions" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Fulfilment instructions">{{ old('fulfilment_instructions') }}</textarea>
                <button class="bg-emerald-600 rounded-xl px-4 py-2 font-semibold">Add package</button>
            </form>
        </div>
    </section>
    @endunless
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-package-form]').forEach((form) => {
    const type = form.querySelector('[name="entitlement_type"]');
    const quantity = form.querySelector('[data-quantity-field]');
    const duration = form.querySelector('[data-duration-field]');
    const sync = () => {
        const quantityInput = quantity.querySelector('input');
        const durationInput = duration.querySelector('input');
        quantity.hidden = type.value !== 'quantity';
        duration.hidden = type.value !== 'time';
        quantityInput.required = type.value === 'quantity';
        durationInput.required = type.value === 'time';
        quantityInput.disabled = type.value !== 'quantity';
        durationInput.disabled = type.value !== 'time';
    };
    type.addEventListener('change', sync);
    sync();
});
</script>
@endpush
