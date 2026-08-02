<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReportPublicPulseAccountInvalidRequest;
use App\Models\PublicPulseSourceAccount;
use App\Services\PublicPulse\PublicPulseAccountService;
use Illuminate\Http\JsonResponse;
class PulseEngineAccountController extends Controller
{
    public function __construct(private PublicPulseAccountService $accounts) {}
    public function index(): JsonResponse { return response()->json(['accounts'=>$this->accounts->engineAccounts()]); }
    public function invalid(ReportPublicPulseAccountInvalidRequest $request, PublicPulseSourceAccount $publicPulseSourceAccount): JsonResponse
    {
        $account = $this->accounts->reportInvalid($publicPulseSourceAccount, $request->validated('reason'));
        return response()->json(['data'=>['id'=>$account->id,'status'=>$account->status]]);
    }
}

