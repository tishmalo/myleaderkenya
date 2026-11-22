<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ImportBulkSmsContactsRequest;
use App\Http\Requests\Web\ImportSupportContactsRequest;
use App\Http\Requests\Web\SaveCallScriptRequest;
use App\Http\Requests\Web\SendBulkSmsRequest;
use App\Http\Requests\Web\StoreAspirantPollRequest;
use App\Http\Requests\Web\StoreCallLogRequest;
use App\Http\Requests\Web\StoreCampaignWebsiteRequest;
use App\Http\Requests\Web\SupportContactRequest;
use App\Models\CandidateSupportContact;
use App\Services\Web\AspirantBulkSmsService;
use App\Services\Web\AspirantPollService;
use App\Services\Web\AspirantToolAccessService;
use App\Services\Web\AspirantToolPageService;
use App\Services\Web\CandidateBulkSmsContactService;
use App\Services\Web\CandidateCallCenterService;
use App\Services\Web\CandidateSupportContactService;
use App\Services\Web\CampaignWebsiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspirantToolController extends Controller
{
    public function __construct(
        private AspirantToolAccessService $access,
        private AspirantToolPageService $page,
        private CandidateBulkSmsContactService $bulkSmsContacts,
        private AspirantBulkSmsService $bulkSms,
        private AspirantPollService $polls,
        private CandidateCallCenterService $callCenter,
        private CampaignWebsiteService $websites,
        private CandidateSupportContactService $supportContacts,
    ) {}

    public function show(Request $request, string $key): View|RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), $key);

        if (! $tool->exists()) {
            abort(404);
        }

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        return view('aspirants.tools.show', $this->page->payload($tool, $request));
    }

    public function importBulkSmsContacts(ImportBulkSmsContactsRequest $request): RedirectResponse
    {
        $candidate = $this->access->candidateForUser($request->user());

        if (! $candidate) {
            return $this->dashboardWarning('No aspirant profile is linked to this account yet.');
        }

        $this->bulkSmsContacts->queueImport(
            $candidate,
            $request->user(),
            $request->file('contacts_file'),
            (int) $request->validated()['support_group_type_id']
        );

        return redirect()->route('aspirant.tools.show', 'bulk-sms')
            ->with('success', 'Contact import queued. The file will be processed securely in the background.');
    }

    public function sendBulkSms(SendBulkSmsRequest $request): RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), 'bulk-sms');

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        $result = $this->bulkSms->send($tool->candidate, $request->user(), $request->validated(), $tool->scope);

        if ($result->failed()) {
            return redirect()->route('aspirant.tools.show', 'bulk-sms')
                ->withInput()
                ->with('warning', $result->warning);
        }

        return redirect()->route('aspirant.tools.show', 'bulk-sms')
            ->with('success', 'Bulk SMS queued for ' . number_format($result->recipientCount)
                . ' recipients. ' . number_format($result->tokensReserved)
                . ' tokens reserved for ' . number_format($result->smsUnits) . ' SMS units.');
    }

    public function storePoll(StoreAspirantPollRequest $request): RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), 'opinion-polls');

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        if ($tool->scopeMissing()) {
            return redirect()->route('aspirant.tools.show', 'opinion-polls')
                ->with('warning', $tool->scopeMessage());
        }

        $poll = $this->polls->store(
            $tool->candidate,
            $request->user(),
            (string) $request->validated()['question'],
            $request->optionLines(),
            $request->status(),
            $tool->scope
        );

        return redirect()->route('aspirant.tools.show', 'opinion-polls')
            ->with('success', $this->polls->resultMessage($poll, $tool->scopeLabel()));
    }

    public function saveCallScript(SaveCallScriptRequest $request): RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), 'call-center');

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        if ($tool->scopeMissing()) {
            return redirect()->route('aspirant.tools.show', 'call-center')
                ->with('warning', $tool->scopeMessage());
        }

        $validated = $request->validated();

        $this->callCenter->saveScript(
            $tool->candidate,
            $request->user(),
            (string) $validated['script'],
            (string) $validated['callback_priority'],
            $tool->scope
        );

        return redirect()->route('aspirant.tools.show', 'call-center')
            ->with('success', 'Call script saved. You can now start the scoped call list.');
    }

    public function storeCallLog(StoreCallLogRequest $request): RedirectResponse|JsonResponse
    {
        $tool = $this->access->resolve($request->user(), 'call-center');

        if ($message = $tool->denialMessage()) {
            return $this->callLogFailure($request, $message, 403);
        }

        if ($tool->scopeMissing()) {
            return $this->callLogFailure($request, (string) $tool->scopeMessage(), 422);
        }

        $callLog = $this->callCenter->recordCall($tool->candidate, $request->user(), $request->validated(), $tool->scope);

        if (! $callLog) {
            return $this->callLogFailure($request, 'That voter is not available in your scoped call list.', 404);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Call log recorded.',
                'log' => [
                    'id' => $callLog->id,
                    'voter' => $callLog->voter_name,
                    'outcome' => str_replace('_', ' ', ucfirst($callLog->outcome)),
                    'callback_at' => $callLog->callback_at?->format('M j, H:i'),
                    'notes' => $callLog->notes,
                ],
            ], 201);
        }

        return redirect()->route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1])
            ->with('success', 'Call log recorded.');
    }

    public function storeWebsiteRequest(StoreCampaignWebsiteRequest $request): RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), 'campaign-website');

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        $this->websites->submit($tool->candidate, $request->user(), $request->validated());

        return redirect()->route('aspirant.tools.show', 'campaign-website')
            ->with('success', 'Campaign website request submitted. An admin will review it and follow up.');
    }

    public function storeSupportContact(SupportContactRequest $request): RedirectResponse
    {
        $tool = $this->access->resolve($request->user(), 'support-groups');

        if ($message = $tool->denialMessage()) {
            return $this->dashboardWarning($message);
        }

        $this->supportContacts->create($tool->candidate, $request->user(), $request->validated());

        return redirect()->route('aspirant.tools.show', 'support-groups')
            ->with('success', 'Support contact added.');
    }

    public function importSupportContacts(ImportSupportContactsRequest $request): RedirectResponse
    {
        $candidate = $this->access->candidateForUser($request->user());

        if (! $candidate) {
            return $this->dashboardWarning('No aspirant profile is linked to this account yet.');
        }

        $this->supportContacts->queueImport(
            $candidate,
            $request->user(),
            $request->file('contacts_file'),
            $request->defaultGroupId()
        );

        return redirect()->route('aspirant.tools.show', 'support-groups')
            ->with('success', 'Support contacts import queued. The file will be processed in the background.');
    }

    public function updateSupportContact(SupportContactRequest $request, CandidateSupportContact $candidateSupportContact): RedirectResponse
    {
        $this->supportContacts->update(
            $candidateSupportContact,
            $this->access->candidateForUser($request->user()),
            $request->user(),
            $request->validated()
        );

        return redirect()->route('aspirant.tools.show', 'support-groups')
            ->with('success', 'Support contact updated.');
    }

    public function destroySupportContact(Request $request, CandidateSupportContact $candidateSupportContact): RedirectResponse
    {
        $this->supportContacts->delete($candidateSupportContact, $this->access->candidateForUser($request->user()));

        return redirect()->route('aspirant.tools.show', 'support-groups')
            ->with('success', 'Support contact removed.');
    }

    public function websiteSamples(): View
    {
        return view('aspirants.tools.website-samples', ['samples' => $this->websites->samples()]);
    }

    private function dashboardWarning(string $message): RedirectResponse
    {
        return redirect('/aspirant/dashboard')->with('warning', $message);
    }

    private function callLogFailure(Request $request, string $message, int $status): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route('aspirant.tools.show', ['key' => 'call-center', 'call_list' => 1])
            ->with('warning', $message);
    }
}
