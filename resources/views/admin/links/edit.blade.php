@extends('layouts.app')

@section('page_title', 'Edit Link/Page')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
            <i class="fas fa-pen text-emerald-500"></i> Edit Link/Page
        </h1>
        <a href="{{ route('links.index') }}" class="text-zinc-400 hover:text-white">← Back to Pages &amp; Links</a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300">{{ $errors->first() }}</div>
    @endif

    @include('admin.links._form', ['link' => $link])
</div>
@endsection
