@extends('layouts.app')

@section('page_title', 'Add Payment Method')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <h1 class="text-2xl font-semibold mb-8">Add New Payment Method</h1>

        <form action="{{ route('payment-methods.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Method Name</label>
                    <input type="text" name="name" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. M-Pesa Till Number">
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Payment Type</label>
                    <select name="type" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                        <option value="">Select Type</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Phone Number (mainly for M-Pesa) -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number (M-Pesa)</label>
                    <input type="text" name="phone_number"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="+254 712 345 678">
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Account Number / Till Number</label>
                    <input type="text" name="account_number"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="1234567890">
                </div>

                <!-- Account Name -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Account Name / Payee Name</label>
                    <input type="text" name="account_name"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="John Doe / Tuko Kadi Campaign">
                </div>

                <!-- Bank Name (for bank transfers) -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Bank Name</label>
                    <input type="text" name="bank_name"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Equity Bank / KCB">
                </div>

                <!-- Branch -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Branch (Optional)</label>
                    <input type="text" name="branch"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                           placeholder="Nairobi Branch">
                </div>

                <!-- Instructions -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Payment Instructions</label>
                    <textarea name="instructions" rows="4"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-3xl px-5 py-4 focus:outline-none focus:border-emerald-500"
                              placeholder="Send money to 0712345678 and include your name and phone in the message..."></textarea>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 accent-emerald-500">
                    <label for="is_active" class="text-zinc-300">Active (visible to users)</label>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Display Order</label>
                    <input type="number" name="sort_order" value="0"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="flex gap-4 mt-10">
                <a href="{{ route('payment-methods.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium transition-colors">
                    Save Payment Method
                </button>
            </div>
        </form>
    </div>
</div>
@endsection