@extends('layouts.app')

@section('page_title', 'Create Coalition')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8"><h1 class="text-3xl font-semibold text-white">Create Coalition</h1><a href="{{ route('coalitions.index') }}" class="text-zinc-400 hover:text-white">Back to Coalitions</a></div>
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8"><form action="{{ route('coalitions.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('coalitions._form')<div class="mt-10"><button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold text-lg">Create Coalition</button></div></form></div>
</div>
@endsection
