<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublicPulseWebhookRequest;
use App\Services\PublicPulse\PublicPulseWebhookService;
use Illuminate\Http\Response;
class PulseWebhookController extends Controller
{
    public function __construct(private PublicPulseWebhookService $webhook) {}
    public function __invoke(PublicPulseWebhookRequest $request): Response
    {
        $this->webhook->handle($request->getContent(), $request->header('X-Pulse-Signature'), $request->validated());
        return response()->noContent();
    }
}
