<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAspirantEventRequest;
use App\Services\Web\AspirantEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserEventController extends Controller
{
    public function __construct(private AspirantEventService $events) {}

    public function index(Request $request): View
    {
        return view('account.events.index', [
            'events' => $this->events->listFor($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('account.events.create');
    }

    public function store(StoreAspirantEventRequest $request): RedirectResponse
    {
        $this->events->submit(
            $request->user(),
            $request->validated(),
            $request->file('poster'),
        );

        return redirect()->route('account.events.index')
            ->with('success', 'Your event was submitted and is awaiting administrator approval.');
    }
}
