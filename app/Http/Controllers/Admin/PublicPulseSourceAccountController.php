<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePublicPulseSourceAccountRequest;
use App\Models\PublicPulseSourceAccount;
use App\Services\PublicPulse\XSessionHealthCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(SavePublicPulseSourceAccountRequest $request): RedirectResponse
    {
        $data = $request->payload();
        $account = $this->accounts->create($data + ['status' => PublicPulseSourceAccount::STATUS_NEEDS_REPLACEMENT]);
        $this->accounts->recordHealth($account, $this->healthCheck->check($account));

        return redirect()
            ->route('public-pulse.x-sessions.index')
            ->with('success', 'X session saved and health check completed.');
    }

    public function update(SavePublicPulseSourceAccountRequest $request, PublicPulseSourceAccount $publicPulseSourceAccount): RedirectResponse
    {
        $data = $request->payload();
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
}
