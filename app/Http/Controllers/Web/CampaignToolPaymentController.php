<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CampaignToolPackageCheckoutRequest;
use App\Models\CampaignToolRequest;
use App\Services\Web\CampaignToolCommerceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CampaignToolPaymentController extends Controller
{
    public function __construct(private CampaignToolCommerceService $commerce) {}
    public function checkout(CampaignToolPackageCheckoutRequest $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    { try { return redirect()->away($this->commerce->startCheckout($request->user(),$campaignToolRequest,$request->validated())); } catch(Throwable $e) { return back()->withErrors(['payment'=>$e->getMessage()])->withInput(); } }
    public function callback(Request $request): RedirectResponse
    { try { $result=$this->commerce->completeCallback($request->query()); } catch(Throwable $e) { $result=['status'=>'failed','message'=>$e->getMessage()]; } return redirect()->route('account.toolbox.index')->with($result['status']==='success'?'success':'warning',$result['message']); }
}
