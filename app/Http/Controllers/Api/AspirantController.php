<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AspirantSubmissionRequest;
use App\Http\Requests\Api\AspirantUpdateRequest;
use App\Http\Requests\Api\ListAspirantsRequest;
use App\Models\Candidate;
use App\Services\Admin\CandidateService;
use App\Services\Web\AspirantApiService;
use App\Support\AspirantApiPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AspirantController extends Controller
{
    public function __construct(
        private CandidateService $candidateService,
        private AspirantApiService $aspirants,
    ) {}

    public function list(ListAspirantsRequest $request): JsonResponse
    {
        $aspirants = $this->candidateService
            ->getApprovedAspirantsForApi($request->filters(), $request->perPage())
            ->through(fn (Candidate $candidate) => AspirantApiPresenter::format($candidate));

        return response()->json($aspirants);
    }

    public function store(AspirantSubmissionRequest $request): JsonResponse
    {
        $registration = $this->aspirants->register($request->validated(), $request->allFiles());

        return response()->json([
            'message' => 'Aspirant registration submitted successfully. An admin will review it before it appears publicly.',
            'user' => [
                'id' => $registration['user']->id,
                'username' => $registration['user']->username,
                'user_type' => $registration['user']->user_type,
            ],
            'token' => $registration['token'],
            'data' => AspirantApiPresenter::format($registration['candidate'], includeAbout: true),
        ], 201);
    }

    public function update(AspirantUpdateRequest $request, Candidate $candidate): JsonResponse
    {
        $candidate = $this->aspirants->update($candidate, $request->validated(), $request->allFiles());

        return response()->json([
            'message' => 'Aspirant submission updated successfully. An admin will review it before it appears publicly.',
            'data' => AspirantApiPresenter::format($candidate, includeAbout: true),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $candidate = $this->aspirants->profile($request->user());

        if (! $candidate) {
            return response()->json([
                'message' => 'No aspirant profile is linked to this account yet.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => AspirantApiPresenter::format($candidate, includeAbout: true),
        ]);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $profile = $this->aspirants->publicProfile($candidate);

        return response()->json([
            'data' => array_merge(
                AspirantApiPresenter::format($profile, includeAbout: true),
                ['related_news' => AspirantApiPresenter::relatedArticles($profile->relatedArticles)]
            ),
        ]);
    }
}
