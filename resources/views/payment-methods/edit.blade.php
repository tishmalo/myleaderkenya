@extends('layouts.app')

@section('page_title', 'Edit Payment Method')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-semibold">Edit Payment Method</h1>
            <a href="{{ route('payment-methods.index') }}" 
               class="text-zinc-400 hover:text-white flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <form action="{{ route('payment-methods.update', $paymentMethod) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Method Name</label>
                    <input type="text" name="name" value="{{ old('name', $paymentMethod->name) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Payment Type</label>
                    <select name="type" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                        <option value="mpesa" {{ old('type', $paymentMethod->type) === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank" {{ old('type', $paymentMethod->type) === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="paypal" {{ old('type', $paymentMethod->type) === 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="cash" {{ old('type', $paymentMethod->type) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="other" {{ old('type', $paymentMethod->type) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number (M-Pesa)</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $paymentMethod->phone_number) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Account Number / Till Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $paymentMethod->account_number) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Account Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Account Name / Payee Name</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $paymentMethod->account_name) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Bank Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $paymentMethod->bank_name) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Branch -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Branch</label>
                    <input type="text" name="branch" value="{{ old('branch', $paymentMethod->branch) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Instructions -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Payment Instructions</label>
                    <textarea name="instructions" rows="4"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-3xl px-5 py-4 focus:outline-none focus:border-emerald-500">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }} 
                           class="w-5 h-5 accent-emerald-500">
                    <label for="is_active" class="text-zinc-300">Active (visible to users)</label>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Display Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $paymentMethod->sort_order) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="flex gap-4 mt-10">
                <a href="{{ route('payment-methods.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">
                    Update Payment Method
                </button>
            </div>
        </form>

        <!-- Delete Button -->
        <div class="mt-10 pt-8 border-t border-zinc-800">
            <button onclick="showDeleteModal('{{ route('payment-methods.destroy', $paymentMethod) }}', 'This payment method will be deleted.')"
                    class="w-full py-4 border border-red-600/50 text-red-400 hover:bg-red-950/30 rounded-2xl font-medium flex items-center justify-center gap-2">
                <i class="fas fa-trash"></i>
                Delete This Payment Method
            </button>
        </div>
    </div>
</div>
@endsection