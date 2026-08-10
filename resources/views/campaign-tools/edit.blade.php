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
        <p class="mt-2 text-zinc-400">Adopters pay these prices directly. Bulk SMS packages are managed under token packages.</p>
        <div class="mt-6 grid gap-4">
            @foreach($campaignTool->packages as $package)
            <form method="POST" action="{{ route('campaign-tools.packages.update', [$campaignTool, $package]) }}" class="grid md:grid-cols-4 gap-3 border border-zinc-800 rounded-2xl p-4">@csrf @method('PUT')
                <input name="name" value="{{ $package->name }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Package name">
                <input type="number" step="0.01" min="1" name="price" value="{{ $package->price }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Price">
                <input name="currency" value="{{ $package->currency }}" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="KES">
                <select name="entitlement_type" class="bg-zinc-800 rounded-xl px-3 py-2 text-white">@foreach(\App\Models\CampaignToolPackage::ENTITLEMENT_TYPES as $type)<option value="{{ $type }}" @selected($package->entitlement_type===$type)>{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select>
                <input type="number" min="1" name="entitlement_quantity" value="{{ $package->entitlement_quantity }}" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Quantity">
                <input type="number" min="1" name="duration_days" value="{{ $package->duration_days }}" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Duration days">
                <input type="number" min="0" name="sort_order" value="{{ $package->sort_order }}" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Sort">
                <label class="flex items-center gap-2 text-zinc-300"><input type="checkbox" name="is_active" value="1" @checked($package->is_active)> Active</label>
                <textarea name="description" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Description">{{ $package->description }}</textarea>
                <textarea name="fulfilment_instructions" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Fulfilment instructions">{{ $package->fulfilment_instructions }}</textarea>
                <button class="bg-emerald-600 rounded-xl px-4 py-2 font-semibold">Save package</button>
            </form>
            @endforeach
            <form method="POST" action="{{ route('campaign-tools.packages.store', $campaignTool) }}" class="grid md:grid-cols-4 gap-3 border border-emerald-500/30 rounded-2xl p-4">@csrf
                <input name="name" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="New package name">
                <input type="number" step="0.01" min="1" name="price" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Price">
                <input name="currency" value="KES" required class="bg-zinc-800 rounded-xl px-3 py-2 text-white">
                <select name="entitlement_type" class="bg-zinc-800 rounded-xl px-3 py-2 text-white">@foreach(\App\Models\CampaignToolPackage::ENTITLEMENT_TYPES as $type)<option value="{{ $type }}">{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select>
                <input type="number" min="1" name="entitlement_quantity" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Quantity">
                <input type="number" min="1" name="duration_days" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Duration days">
                <input type="number" min="0" name="sort_order" value="0" class="bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Sort">
                <label class="flex items-center gap-2 text-zinc-300"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <textarea name="description" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Description"></textarea>
                <textarea name="fulfilment_instructions" class="md:col-span-2 bg-zinc-800 rounded-xl px-3 py-2 text-white" placeholder="Fulfilment instructions"></textarea>
                <button class="bg-emerald-600 rounded-xl px-4 py-2 font-semibold">Add package</button>
            </form>
        </div>
    </section>
    @endunless
</div>
@endsection
