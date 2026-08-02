<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SmsBalanceRequestUpdateRequest;
use App\Models\CandidateSmsBalanceRequest;
use App\Services\Web\SmsBalanceRequestService;

class SmsBalanceRequestController extends Controller
{
    public function __construct(private SmsBalanceRequestService $requests) {}

    public function index()
    {
        $requests = $this->requests->paginate(request()->only(['search', 'status']));

        return view('sms-balance-requests.index', compact('requests'));
    }

    public function update(SmsBalanceRequestUpdateRequest $request, CandidateSmsBalanceRequest $candidateSmsBalanceRequest)
    {
        $this->requests->updateFromAdmin($candidateSmsBalanceRequest, $request->validated());

        return redirect()->route('sms-balance-requests.index')->with('success', 'SMS balance request updated.');
    }
}
