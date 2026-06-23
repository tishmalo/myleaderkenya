@extends('layouts.app')

@section('page_title', 'Donate Page Settings')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-zinc-900 border border-zinc-800 overflow-hidden shadow-sm rounded-3xl">
                <div class="p-8 text-zinc-100">
                    
                    @if(session('success'))
                        <div class="mb-6 p-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.donate.update') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="donation_why_text" class="block text-sm font-medium text-zinc-300">"Why Donate?" Description Text</label>
                            <textarea id="donation_why_text" name="donation_why_text" rows="6" class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4" required>{{ $donateWhyText }}</textarea>
                            @error('donation_why_text')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="donation_whatsapp_link" class="block text-sm font-medium text-zinc-300">WhatsApp Group Link</label>
                            <input type="url" id="donation_whatsapp_link" name="donation_whatsapp_link" value="{{ $whatsappLink }}" class="mt-2 block w-full rounded-2xl border-zinc-700 bg-zinc-950 text-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4" required>
                            @error('donation_whatsapp_link')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 border border-transparent rounded-2xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-zinc-900 transition ease-in-out duration-150">
                                Save Settings
                            </button>
                        </div>
                    </form>

                    <hr class="my-10 border-zinc-800">

                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-zinc-100">Payment Methods</h2>
                            <button onclick="openAddModal()" class="px-4 py-2 bg-emerald-600/10 text-emerald-500 border border-emerald-500/20 rounded-xl text-sm font-medium hover:bg-emerald-600/20 transition">
                                + Add New Method
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($paymentMethods as $method)
                                <div class="p-6 bg-zinc-950 border border-zinc-800 rounded-3xl relative group">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <span class="px-2 py-1 bg-zinc-800 text-zinc-400 rounded-md text-[10px] uppercase font-bold tracking-wider mb-2 inline-block">
                                                {{ $method->type }}
                                            </span>
                                            <h3 class="text-lg font-medium text-zinc-200">{{ $method->name }}</h3>
                                        </div>
                                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                            <button onclick="editPaymentMethod({{ json_encode($method) }})" class="p-2 text-zinc-400 hover:text-emerald-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('admin.payment-methods.destroy', $method) }}" method="POST" onsubmit="return confirm('Delete this payment method?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 text-zinc-400 hover:text-red-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-1 text-sm text-zinc-500">
                                        @if($method->account_number) <p>Acc: {{ $method->account_number }}</p> @endif
                                        @if($method->account_name) <p>Name: {{ $method->account_name }}</p> @endif
                                        @if($method->phone_number) <p>Phone: {{ $method->phone_number }}</p> @endif
                                        @if($method->instructions) <p class="line-clamp-2 italic">"{{ $method->instructions }}"</p> @endif
                                    </div>

                                    @if(!$method->is_active)
                                        <span class="absolute top-4 right-4 px-2 py-1 bg-red-500/10 text-red-500 rounded-md text-[10px] font-bold">INACTIVE</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="add-payment-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-zinc-900 border border-zinc-800 w-full max-w-lg rounded-3xl shadow-2xl p-8 max-h-[90vh] overflow-y-auto">
            <h2 id="modal-title" class="text-2xl font-semibold mb-6">Add Payment Method</h2>
            
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <ul class="text-xs text-red-500 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="payment-form" action="{{ route('admin.payment-methods.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Display Name</label>
                        <input type="text" name="name" id="p-name" required class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Type</label>
                        <select name="type" id="p-type" required class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                            <option value="mpesa">MPESA</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Account Number</label>
                        <input type="text" name="account_number" id="p-acc-no" class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Account Name</label>
                        <input type="text" name="account_name" id="p-acc-name" class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Phone Number</label>
                        <input type="text" name="phone_number" id="p-phone" class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" id="p-bank" class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-zinc-400 mb-1">Instructions</label>
                    <textarea name="instructions" id="p-instructions" rows="3" class="w-full bg-zinc-950 border-zinc-800 rounded-xl p-3 text-sm focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="p-active" value="1" checked class="rounded border-zinc-800 bg-zinc-950 text-emerald-600 focus:ring-emerald-500">
                    <label class="text-sm text-zinc-300">Is Active</label>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="button" onclick="closeModal()" class="flex-1 py-3 border border-zinc-800 rounded-2xl font-medium hover:bg-zinc-800 transition">Cancel</button>
                    <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 rounded-2xl font-medium transition">Save Method</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const baseAction = "{{ route('admin.payment-methods.store') }}";

        function openAddModal() {
            closeModal();
            document.getElementById('add-payment-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('add-payment-modal').classList.add('hidden');
            document.getElementById('payment-form').reset();
            document.getElementById('form-method').value = 'POST';
            document.getElementById('payment-form').action = baseAction;
            document.getElementById('modal-title').innerText = 'Add Payment Method';
        }

        function editPaymentMethod(method) {
            closeModal();
            document.getElementById('add-payment-modal').classList.remove('hidden');
            document.getElementById('modal-title').innerText = 'Edit Payment Method';
            
            const form = document.getElementById('payment-form');
            form.action = baseAction + '/' + method.id;
            document.getElementById('form-method').value = 'PUT';
            
            document.getElementById('p-name').value = method.name;
            document.getElementById('p-type').value = method.type;
            document.getElementById('p-acc-no').value = method.account_number || '';
            document.getElementById('p-acc-name').value = method.account_name || '';
            document.getElementById('p-phone').value = method.phone_number || '';
            document.getElementById('p-bank').value = method.bank_name || '';
            document.getElementById('p-instructions').value = method.instructions || '';
            document.getElementById('p-active').checked = !!method.is_active;
        }
    </script>
@endsection
