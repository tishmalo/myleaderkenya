<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\RecaptchaService;
use App\Services\Web\SpamFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BotVerifyController extends Controller
{
    public function __construct(
        private RecaptchaService $recaptcha,
        private SpamFilterService $spamFilter
    ) {}

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'g-recaptcha-response' => ['required', 'string'],
            'intended' => ['nullable', 'string', 'max:1000'],
        ]);

        $ip = $request->ip();

        if (! $this->recaptcha->verify($data['g-recaptcha-response'])) {
            return back()->withErrors(['g-recaptcha-response' => 'Verification failed. Please try again.']);
        }

        if ($ip) {
            $this->spamFilter->unblockIp($ip);
        }

        $intended = $data['intended'] ?? '';

        if ($intended !== '' && str_starts_with($intended, '/')) {
            return redirect($intended);
        }

        return redirect()->route('landing');
    }
}