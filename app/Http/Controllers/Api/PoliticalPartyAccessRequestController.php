<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PoliticalParty\StorePartyAccessRequest;
use App\Http\Resources\PoliticalPartyAccessRequestResource;
use App\Models\PoliticalParty;
use App\Services\Web\PoliticalPartyManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PoliticalPartyAccessRequestController extends Controller
{
    public function __construct(
        private PoliticalPartyManagementService $management,
    ) {}

    public function store(
        StorePartyAccessRequest $request,
        PoliticalParty $politicalParty,
    ): JsonResponse {
        $accessRequest = $this->management->requestAccess(
            $politicalParty,
            $request->validated(),
            $request->file('authorization_document'),
        );

        return response()->json([
            'message' => 'Political party access request submitted for review.',
            'data' => (new PoliticalPartyAccessRequestResource($accessRequest))
                ->resolve($request),
        ], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return PoliticalPartyAccessRequestResource::collection(
            $this->management->accountRequestsForUser($request->user()),
        );
    }
}
