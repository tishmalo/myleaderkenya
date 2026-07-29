<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Web\PublicPulseMentionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\PublicPulse\PublicPulseMentionClassificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPulseController extends Controller
{
    public function __construct(
        private PublicPulseMentionRepositoryInterface $mentionRepository,
        private PublicPulseMentionClassificationService $classificationService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['language', 'tone', 'sentiment', 'topic', 'low_confidence', 'search']);
        $mentions = $this->mentionRepository->paginateForAdmin($filters);

        return view('public-pulse.index', compact('mentions', 'filters'));
    }

    public function reclassify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mentions' => ['required', 'array', 'min:1'],
            'mentions.*' => ['integer', 'distinct', 'exists:public_pulse_mentions,id'],
        ]);

        $result = $this->classificationService->classifyMentionIds($data['mentions'], true);

        return redirect()
            ->route('public-pulse.index')
            ->with('success', sprintf(
                'Reclassification complete: %d classified, %d cached, %d skipped.',
                $result['classified'],
                $result['cached'],
                $result['skipped']
            ));
    }
}
