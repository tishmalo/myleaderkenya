<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Claim your aspirant account</h1>
        <p class="mt-2 text-sm text-gray-600">Set a password to access your campaign dashboard.</p>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <p class="text-sm font-semibold text-gray-900">{{ $candidate->name }}</p>
        <p class="mt-1 text-sm text-gray-600">{{ $candidate->position->name ?? 'Aspirant' }}</p>
        @if($candidate->politicalParty)
            <p class="mt-1 text-sm text-gray-600">{{ $candidate->politicalParty->name }}</p>
        @endif
        <p class="mt-1 text-sm text-gray-600">{{ $candidate->email }}</p>
    </div>

    <form method="POST" action="{{ route('aspirants.claim.store', [$candidate, $token]) }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
            Claim Account
        </button>
    </form>
</x-guest-layout>
