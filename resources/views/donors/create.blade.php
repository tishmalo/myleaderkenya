@extends('layouts.app')

@section('page_title', 'Add New Donor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <h1 class="text-2xl font-semibold mb-8">Record New Donation</h1>

        <form action="{{ route('donors.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Donor Full Name</label>
                    <input type="text" name="name" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email Address</label>
                    <input type="email" name="email"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="john@example.com">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone Number</label>
                    <input type="tel" name="phone"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="+254 712 345 678">
                </div>

                <!-- Payment Method -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Payment Method</label>
                    <select name="payment_method" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Select payment method</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Amount (KES)</label>
                    <input type="number" name="amount" step="0.01" min="1" required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="5000">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Status</label>
                    <select name="status" required
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <!-- Details / Purpose -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Purpose / Notes</label>
                    <textarea name="details" rows="4"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-3xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                              placeholder="Donation for campaign materials..."></textarea>
                </div>

                <!-- Payment Details (JSON friendly - you can store receipt number, transaction id, etc.) -->
                <div class="md:col-span-2">
                    <label class="block text-sm text-zinc-400 mb-2">Payment Reference / Receipt No.</label>
                    <input type="text" name="payment_details[receipt_number]"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-emerald-500 transition-colors"
                           placeholder="MPESA Receipt: ABC123456">
                    <p class="text-xs text-zinc-500 mt-2">You can add more fields later using JSON structure.</p>
                </div>
            </div>

            <div class="flex gap-4 mt-10">
                <a href="{{ route('dashboard.donors') }}" 
                   class="flex-1 py-4 border border-zinc-700 rounded-2xl text-center font-medium hover:bg-zinc-800 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium transition-colors">
                    Save Donation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection