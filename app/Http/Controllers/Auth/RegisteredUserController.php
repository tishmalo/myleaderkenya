<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\PoliticalParty;
use App\Models\Position;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function create(): View
    {
        return view('auth.register', [
            'positions' => Position::ordered()->get(),
            'politicalParties' => PoliticalParty::published()->ordered()->get(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $this->authService->register([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        return redirect(route('my-account', absolute: false));
    }
}
