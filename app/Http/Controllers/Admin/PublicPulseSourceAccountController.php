<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\PublicPulseSourceAccount;
use App\Services\PublicPulse\XSessionHealthCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicPulseSourceAccountController extends Controller
{
    public function __construct(
        private PublicPulseSourceAccountRepositoryInterface $accounts,
        private XSessionHealthCheckService $healthCheck
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['provider', 'status', 'search']);
        $accounts = $this->accounts->paginate($filters);

        return view('public-pulse.x-sessions', compact('accounts', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $account = $this->accounts->create($data + ['status' => PublicPulseSourceAccount::STATUS_NEEDS_REPLACEMENT]);
        $this->accounts->recordHealth($account, $this->healthCheck->check($account));

        return redirect()
            ->route('public-pulse.x-sessions.index')
            ->with('success', 'X session saved and health check completed.');
    }

    public function update(Request $request, PublicPulseSourceAccount $publicPulseSourceAccount): RedirectResponse
    {
        $data = $this->validatedData($request);
        $account = $this->accounts->update($publicPulseSourceAccount, $data + [
            'replaced_at' => null,
            'issue_notified_at' => null,
        ]);
        $this->accounts->recordHealth($account, $this->healthCheck->check($account));

        return redirect()
            ->route('public-pulse.x-sessions.index')
            ->with('success', 'X session replaced and health check completed.');
    }

    public function check(PublicPulseSourceAccount $publicPulseSourceAccount): RedirectResponse
    {
        $this->accounts->recordHealth($publicPulseSourceAccount, $this->healthCheck->check($publicPulseSourceAccount));

        return redirect()
            ->route('public-pulse.x-sessions.index')
            ->with('success', 'X session health check completed.');
    }

    public function replace(PublicPulseSourceAccount $publicPulseSourceAccount): RedirectResponse
    {
        $this->accounts->update($publicPulseSourceAccount, [
            'status' => PublicPulseSourceAccount::STATUS_NEEDS_REPLACEMENT,
            'replaced_at' => now(),
        ]);

        return redirect()
            ->route('public-pulse.x-sessions.index')
            ->with('success', 'X session removed from the active pool.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'source_key' => ['nullable', 'string', 'max:80'],
            'provider' => ['required', 'string', 'max:80', Rule::in(['x_twscrape', 'x_nitter', 'x_snscrape'])],
            'label' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:120'],
            'encrypted_session_payload' => ['required', 'string', 'min:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]) + ['source_key' => 'x'];
    }
}
