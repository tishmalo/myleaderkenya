@extends('layouts.app')

@section('page_title', 'Edit Donor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-semibold">Edit Donor Record</h1>
            <a href="{{ route('donors.index') }}" 
               class="text-zinc-400 hover:text-white flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <form action="{{ route('donors.update', $donor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Donor Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $donor->name) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $donor->email) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="john@example.com">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $donor->phone) }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="+254 712 345 678">
                </div>

                <!-- Payment Method -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Payment Method</label>
                    <select name="payment_method" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="mpesa" {{ old('payment_method', $donor->payment_method) === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank_transfer" {{ old('payment_method', $donor->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="paypal" {{ old('payment_method', $donor->payment_method) === 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="cash" {{ old('payment_method', $donor->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="other" {{ old('payment_method', $donor->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Amount (KES)</label>
                    <input type="number" name="amount" step="0.01" min="1" 
                           value="{{ old('amount', $donor->amount) }}" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Status</label>
                    <select name="status" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="completed" {{ old('status', $donor->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ old('status', $donor->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ old('status', $donor->status) === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ old('status', $donor->status) === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <!-- Details / Purpose -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Purpose / Notes</label>
                    <textarea name="details" rows="4"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-3xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">{{ old('details', $donor->details) }}</textarea>
                </div>

                <!-- Payment Details -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Payment Reference / Receipt No.</label>
                    <input type="text" 
                           name="payment_details[receipt_number]"
                           value="{{ old('payment_details.receipt_number', $donor->payment_details['receipt_number'] ?? '') }}"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="MPESA Receipt: ABC123456 or Bank Ref: XYZ789">
                </div>
            </div>

            <div class="flex gap-4 mt-10">
                <a href="{{ route('donors.index') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium transition-colors">
                    Update Donation
                </button>
            </div>
        </form>

        <!-- Delete Button -->
        <div class="mt-8 pt-8 border-t border-zinc-800">
            <button onclick="showDeleteModal('{{ route('donors.destroy', $donor) }}', 'This donor record will be permanently deleted.')"
                    class="w-full py-4 border border-red-600/50 text-red-400 hover:bg-red-950/30 rounded-2xl font-medium transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-trash"></i>
                Delete This Donor Record
            </button>
        </div>
    </div>
</div>
@endsection