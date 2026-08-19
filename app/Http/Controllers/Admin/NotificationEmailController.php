<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestNotificationEmailRequest;
use App\Http\Requests\Admin\UpdateNotificationEmailRequest;
use App\Mail\TestNotificationMail;
use App\Services\Admin\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NotificationEmailController extends Controller
{
    public function __construct(private SettingService $settings) {}

    public function index(): View
    {
        return view('admin.notifications.index', [
            'notifications' => $this->settings->getNotificationEmails(),
        ]);
    }

    public function edit(string $key): View
    {
        return view('admin.notifications.edit', [
            'notification' => $this->settings->getNotificationEmail($key),
        ]);
    }

    public function update(UpdateNotificationEmailRequest $request, string $key): RedirectResponse
    {
        $this->settings->updateNotificationEmail($key, $request->validated());

        return redirect()->route('notification-emails.index')
            ->with('success', 'Email notification updated successfully.');
    }

    public function toggle(Request $request, string $key): RedirectResponse
    {
        $this->settings->updateNotificationEmail($key, ['enabled' => $request->boolean('enabled')]);

        return back()->with('success', 'Email notification updated successfully.');
    }

    public function sendTest(SendTestNotificationEmailRequest $request, string $key): RedirectResponse
    {
        $notification = $this->settings->getNotificationEmail($key);
        $samples = $notification['samples'] ?? [];
        $email = $request->validated()['email'];

        Mail::to($email)->send(new TestNotificationMail(
            strtr($notification['subject'], $samples),
            strtr($notification['body'], $samples),
        ));

        return back()->with('success', 'Test email sent to ' . $email . '.');
    }
}
