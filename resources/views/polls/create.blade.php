@extends('layouts.app')

@section('page_title', 'Create Poll')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-semibold text-white">Create Poll</h1>
        <a href="{{ route('polls.index') }}" class="text-zinc-400 hover:text-white">← Back to Polls</a>
    </div>

    @include('polls._form', ['poll' => null])
</div>
@endsection
