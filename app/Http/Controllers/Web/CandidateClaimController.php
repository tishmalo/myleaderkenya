<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class CandidateClaimController extends Controller
{
    public function show(Candidate $candidate, string $token): View
    {
        $this->abortUnlessClaimable($candidate, $token);

        $candidate->load(['position', 'politicalParty']);

        return view('aspirants.claim', [
            'candidate' => $candidate,
            'token' => $token,
        ]);
    }

    public function store(Request $request, Candidate $candidate, string $token): RedirectResponse
    {
        $this->abortUnlessClaimable($candidate, $token);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($candidate, $validated): User {
            $email = Str::lower(trim((string) $candidate->email));
            $emailHash = hash('sha256', $email);

            $user = User::where('email_hash', $emailHash)->first();

            if (! $user) {
                $user = new User();
                $user->name = $candidate->name;
                $user->username = $this->uniqueUsername($candidate->name);
                $user->email = $email;
                $user->role = 'user';
                $user->phone = $candidate->phone;
            }

            $user->name = $user->name ?: $candidate->name;
            $user->phone = $user->phone ?: $candidate->phone;
            $user->password = $validated['password'];
            $user->is_aspirant = true;
            $user->email_verified_at = $user->email_verified_at ?: now();
            $user->save();

            $candidate->forceFill([
                'user_id' => $user->id,
                'claim_token_hash' => null,
                'claim_token_expires_at' => null,
                'claim_sent_at' => null,
                'claimed_at' => now(),
            ])->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('aspirant.dashboard')
            ->with('success', 'Your aspirant account has been claimed successfully.');
    }

    private function abortUnlessClaimable(Candidate $candidate, string $token): void
    {
        if ($candidate->user_id || $candidate->claimed_at) {
            abort(410, 'This aspirant account has already been claimed.');
        }

        if (blank($candidate->email) || blank($candidate->claim_token_hash) || ! $candidate->claim_token_expires_at) {
            abort(404);
        }

        if (now()->greaterThan($candidate->claim_token_expires_at)) {
            abort(410, 'This claim link has expired. Ask an admin to send a new link.');
        }

        if (! hash_equals((string) $candidate->claim_token_hash, hash('sha256', $token))) {
            abort(404);
        }
    }

    private function uniqueUsername(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 40, '');

        if ($base === '') {
            $base = 'aspirant';
        }

        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $suffix++;
        }

        return $username;
    }
}
