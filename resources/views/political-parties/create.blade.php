@extends('layouts.app')

@section('page_title', 'Create Political Party')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8"><h1 class="text-3xl font-semibold text-white">Create Political Party</h1><a href="{{ route('political-parties.index') }}" class="text-zinc-400 hover:text-white">Back to Political Parties</a></div>
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('political-parties.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('political-parties._form')
            <div class="mt-10"><button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg">Create Political Party</button></div>
        </form>
    </div>
</div>
@endsection
