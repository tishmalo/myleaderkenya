<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SmtpUpdateRequest;
use App\Services\Admin\SmtpService;

class SmtpController extends Controller
{
    public function __construct(
        private SmtpService $smtpService
    ) {}

    public function index()
    {
        return view('admin.smtp');
    }

    public function update(SmtpUpdateRequest $request)
    {
        $this->smtpService->updateSettings($request->validated());

        return back()->with('success', 'SMTP settings updated successfully!');
    }
}