<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AspirantRegisterRequest;
use App\Models\Candidate;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Models\User;
use App\Services\Admin\CandidateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AspirantRegistrationController extends Controller
{
    public function __construct(private CandidateService $candidateService) {}

    public function create(): View
    {
        return view('aspirants.register', [
            'positions' => Position::ordered()->get(),
            'politicalParties' => PoliticalParty::published()->ordered()->get(),
        ]);
    }

    public function store(AspirantRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $candidate = DB::transaction(function () use ($request, $validated): Candidate {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $this->uniqueUsername($validated['name']),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'user',
                'phone' => $validated['phone'] ?? null,
                'is_aspirant' => true,
            ]);

            $candidateData = collect($validated)
                ->only([
                    'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id',
                    'about', 'county', 'constituency', 'ward',
                ])
                ->all();

            $candidateData['user_id'] = $user->id;
            $candidateData['approval_status'] = 'pending';

            $candidate = $this->candidateService->createCandidate(
                $candidateData,
                $request->file('profile_picture')
            );

            Auth::login($user);

            return $candidate;
        });

        return redirect()->route('aspirant.dashboard')
            ->with('success', 'Your aspirant registration has been submitted. An admin will review and approve it before it appears publicly.');
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
