<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RedeemCampaignToolPackageRequest;
use App\Models\CampaignToolRequest;
use App\Services\Web\CampaignToolCommerceService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CampaignToolPaymentController extends Controller
{
    public function __construct(private CampaignToolCommerceService $commerce) {}
    public function redeem(RedeemCampaignToolPackageRequest $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    { try { $this->commerce->redeemPackage($request->user(),$campaignToolRequest,(int)$request->validated('package_id')); return back()->with('success','Package funded from your Toolbox. Admin will activate it after setup.'); } catch(Throwable $e) { return back()->withErrors(['payment'=>$e->getMessage()]); } }
}
