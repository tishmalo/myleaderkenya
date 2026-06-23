<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->authService->updatePassword($request->user(), $request->password);

        return back()->with('status', 'password-updated');
    }
}
