<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\UpdateUserProfileRequest;
use App\Services\Web\DashboardDestinationService;
use App\Services\Web\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function __construct(
        private UserProfileService $profiles,
        private DashboardDestinationService $destinations,
    ) {}

    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return view('account.profile.edit', $this->profiles->formData($request->user()));
    }

    public function update(UpdateUserProfileRequest $request): RedirectResponse
    {
        $wasComplete = $this->profiles->isComplete($request->user());
        $user = $this->profiles->update($request->user(), $request->validated());

        if (! $wasComplete) {
            return redirect($this->destinations->urlFor($user, absolute: false))
                ->with('success', 'Your profile is complete. You can now use your dashboard tools.');
        }

        return redirect()->route('account.profile.edit')
            ->with('success', 'Your profile has been updated.');
    }
}