@extends('layouts.app')

@section('page_title', 'Payment Methods')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-semibold">Payment Methods</h1>
            <p class="text-zinc-400">Donation channels users can use to pay</p>
        </div>
        <a href="{{ route('payment-methods.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl flex items-center gap-3">
            <i class="fas fa-plus"></i>
            Add New Method
        </a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-800">
                    <th class="px-8 py-5 text-left">Method</th>
                    <th class="px-8 py-5 text-left">Account / Phone</th>
                    <th class="px-8 py-5 text-left">Instructions</th>
                    <th class="px-8 py-5 text-center">Status</th>
                    <th class="px-8 py-5 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @foreach($paymentMethods as $method)
                <tr class="hover:bg-zinc-800/50">
                    <td class="px-8 py-6">
                        <div class="font-medium">{{ $method->name }}</div>
                        <div class="text-sm text-zinc-500 capitalize">{{ $method->type }}</div>
                    </td>
                    <td class="px-8 py-6">
                        @if($method->phone_number)
                            <div>{{ $method->phone_number }}</div>
                        @elseif($method->account_number)
                            <div>{{ $method->account_number }} - {{ $method->account_name }}</div>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-zinc-400 text-sm">
                        {{ Str::limit($method->instructions, 80) }}
                    </td>
                    <td class="px-8 py-6 text-center">
                        @if($method->is_active)
                            <span class="px-4 py-1 bg-emerald-500/10 text-emerald-400 rounded-2xl text-xs">Active</span>
                        @else
                            <span class="px-4 py-1 bg-red-500/10 text-red-400 rounded-2xl text-xs">Inactive</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-right space-x-4">
                        <a href="{{ route('payment-methods.edit', $method) }}" class="text-emerald-400 hover:text-emerald-500">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="showDeleteModal('{{ route('payment-methods.destroy', $method) }}', 'Delete this payment method?')"
                                class="text-red-400 hover:text-red-500">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection