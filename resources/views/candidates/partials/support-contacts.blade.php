@php
    $canViewSupportGroups = auth()->user()?->canAccess('support-groups.view');
    $canManageSupportGroups = auth()->user()?->canAccess('support-groups.create') || auth()->user()?->canAccess('support-groups.update') || auth()->user()?->canAccess('support-groups.delete');
    $existingSupportContacts = collect(old('support_contacts', isset($candidate) ? $candidate->supportContacts()->with('groupType')->get()->map(fn ($contact) => [
        'id' => $contact->id,
        'support_group_type_id' => $contact->support_group_type_id,
        'name' => $contact->name,
        'email' => $contact->email,
        'phone' => $contact->phone,
    ])->all() : []));
    if ($existingSupportContacts->isEmpty()) {
        $existingSupportContacts = collect([['id' => null, 'support_group_type_id' => '', 'name' => '', 'email' => '', 'phone' => '']]);
    }
@endphp

@if($canViewSupportGroups)
    <div class="mt-8 rounded-3xl border border-zinc-800 bg-zinc-950 p-6" data-support-contacts-panel>
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 text-xl font-semibold text-white">
                    <i class="fas fa-people-group text-emerald-500"></i>
                    Support Groups
                </h2>
                <p class="mt-1 text-sm text-zinc-500">Private campaign contacts grouped by admin-defined categories.</p>
            </div>
            @if($canManageSupportGroups)
                <button type="button" class="rounded-2xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-300 hover:bg-emerald-950" data-add-support-contact>
                    <i class="fas fa-plus"></i> Add Contact
                </button>
            @endif
        </div>

        @if(! $canManageSupportGroups)
            <p class="rounded-2xl border border-amber-800 bg-amber-950/40 p-4 text-sm text-amber-100">You can view support contacts, but your role cannot change them.</p>
        @endif

        @if($supportGroupTypes->isEmpty())
            <p class="rounded-2xl border border-amber-800 bg-amber-950/40 p-4 text-sm text-amber-100">Add active support group types before assigning contacts.</p>
        @else
            <div class="space-y-4" data-support-contact-list>
                @foreach($existingSupportContacts as $index => $contact)
                    <div class="support-contact-row rounded-2xl border border-zinc-800 bg-zinc-900 p-4" data-support-contact-row>
                        <input type="hidden" name="support_contacts[{{ $index }}][id]" value="{{ $contact['id'] ?? '' }}" {{ $canManageSupportGroups ? '' : 'disabled' }}>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="mb-2 block text-sm text-zinc-400">Group</label>
                                <select name="support_contacts[{{ $index }}][support_group_type_id]" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white" {{ $canManageSupportGroups ? '' : 'disabled' }}>
                                    <option value="">Select group</option>
                                    @foreach($supportGroupTypes as $type)
                                        <option value="{{ $type->id }}" {{ (string) ($contact['support_group_type_id'] ?? '') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm text-zinc-400">Name</label>
                                <input type="text" name="support_contacts[{{ $index }}][name]" value="{{ $contact['name'] ?? '' }}" maxlength="255" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white" {{ $canManageSupportGroups ? '' : 'readonly disabled' }}>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm text-zinc-400">Email</label>
                                <input type="email" name="support_contacts[{{ $index }}][email]" value="{{ $contact['email'] ?? '' }}" maxlength="255" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white" {{ $canManageSupportGroups ? '' : 'readonly disabled' }}>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm text-zinc-400">Phone</label>
                                <div class="flex gap-2">
                                    <input type="tel" name="support_contacts[{{ $index }}][phone]" value="{{ $contact['phone'] ?? '' }}" maxlength="50" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white" {{ $canManageSupportGroups ? '' : 'readonly disabled' }}>
                                    @if($canManageSupportGroups)
                                        <button type="button" class="rounded-2xl border border-red-800 px-3 text-red-300 hover:bg-red-950" data-remove-support-contact aria-label="Remove contact"><i class="fas fa-trash"></i></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($canManageSupportGroups && $supportGroupTypes->isNotEmpty())
        <template data-support-contact-template>
            <div class="support-contact-row rounded-2xl border border-zinc-800 bg-zinc-900 p-4" data-support-contact-row>
                <input type="hidden" data-name="id" value="">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-sm text-zinc-400">Group</label>
                        <select data-name="support_group_type_id" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                            <option value="">Select group</option>
                            @foreach($supportGroupTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-zinc-400">Name</label>
                        <input type="text" data-name="name" maxlength="255" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-zinc-400">Email</label>
                        <input type="email" data-name="email" maxlength="255" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-zinc-400">Phone</label>
                        <div class="flex gap-2">
                            <input type="tel" data-name="phone" maxlength="50" class="w-full rounded-2xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">
                            <button type="button" class="rounded-2xl border border-red-800 px-3 text-red-300 hover:bg-red-950" data-remove-support-contact aria-label="Remove contact"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif
@endif
