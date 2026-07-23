<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublicApprovalRequest;
use App\Services\Web\PublicApprovalService;
use Illuminate\Http\JsonResponse;

class PublicApprovalController extends Controller
{
    public function __construct(
        private PublicApprovalService $publicApprovalService
    ) {}

    public function presidential(PublicApprovalRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->publicApprovalService->presidentialScores(),
        ]);
    }
}
