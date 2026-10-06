<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public, shareable poll page. Unlike the homepage sections, this page
 * is reachable by a stable slug and carries per-poll SEO metadata so a poll
 * can be shared on WhatsApp, X and Facebook with a useful preview.
 */
class PollController extends Controller
{
    public function __construct(private readonly PollService $pollService) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user() === null) {
            return redirect()->guest(route('login'))
                ->with('status', 'Log in to see the polls for your area - Presidential, Governor, Senator, Women Rep, MP and MCA - and cast your vote.');
        }

        $viewer = $request->user();

        return view('polls.public.index', [
            'polls' => $this->pollService->homepagePolls($viewer),
            'needsLocationPrompt' => $this->pollService->needsLocationPrompt($viewer),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $poll = $this->pollService->presentPollBySlug($slug, $request->user());

        abort_unless($poll !== null, 404);

        return view('polls.show', [
            'poll' => $poll,
            'shareUrl' => route('poll.show', $poll['slug']),
        ]);
    }
}
