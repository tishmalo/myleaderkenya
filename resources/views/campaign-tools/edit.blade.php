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
</div>
@endsection