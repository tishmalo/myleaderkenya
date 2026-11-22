<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BotVerifyRequest;
use App\Services\Web\RecaptchaService;
use App\Services\Web\SpamFilterService;
use Illuminate\Http\RedirectResponse;

class BotVerifyController extends Controller
{
    public function __construct(
        private RecaptchaService $recaptcha,
        private SpamFilterService $spamFilter
    ) {}

    public function verify(BotVerifyRequest $request): RedirectResponse
    {
        $ip = $request->ip();

        if (! $this->recaptcha->verify($request->token())) {
            return back()->withErrors(['g-recaptcha-response' => 'Verification failed. Please try again.']);
        }

        if ($ip) {
            $this->spamFilter->unblockIp($ip);
        }

        $intended = $request->intended();

        if ($intended !== '' && str_starts_with($intended, '/')) {
            return redirect($intended);
        }

        return redirect()->route('landing');
    }
}