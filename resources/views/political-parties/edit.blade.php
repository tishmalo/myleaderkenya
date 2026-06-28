@extends('layouts.app')

@section('page_title', 'Edit Political Party')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8"><h1 class="text-3xl font-semibold text-white">Edit Political Party</h1><a href="{{ route('political-parties.index') }}" class="text-zinc-400 hover:text-white">Back to Political Parties</a></div>
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('political-parties.update', $politicalParty) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('political-parties._form', ['politicalParty' => $politicalParty])
            <div class="mt-10 flex gap-4"><a href="{{ route('political-parties.index') }}" class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">Cancel</a><button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">Update Political Party</button></div>
        </form>
    </div>
</div>
@endsection
