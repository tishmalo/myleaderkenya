<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Web\CampaignToolCommerceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignToolPackageRequest;
use App\Models\CampaignTool;
use App\Models\CampaignToolPackage;
use Illuminate\Http\RedirectResponse;

class CampaignToolPackageController extends Controller
{
    public function __construct(private CampaignToolCommerceRepositoryInterface $commerce) {}
    public function store(CampaignToolPackageRequest $request, CampaignTool $campaignTool): RedirectResponse
    {
        abort_if(str_contains(strtolower($campaignTool->slug.' '.$campaignTool->title), 'bulk-sms'), 422, 'Bulk SMS uses token packages.');
        $data=$request->validated(); $data['is_active']=$request->boolean('is_active'); $data['campaign_tool_id']=$campaignTool->id;
        $this->commerce->createPackage($data);
        return back()->with('success','Campaign tool package created.');
    }
    public function update(CampaignToolPackageRequest $request, CampaignTool $campaignTool, CampaignToolPackage $package): RedirectResponse
    {
        abort_unless($package->campaign_tool_id === $campaignTool->id, 404);
        $data=$request->validated(); $data['is_active']=$request->boolean('is_active');
        $this->commerce->updatePackage($package,$data);
        return back()->with('success','Campaign tool package updated.');
    }
    public function destroy(CampaignTool $campaignTool, CampaignToolPackage $package): RedirectResponse
    {
        abort_unless($package->campaign_tool_id === $campaignTool->id,404);
        if ($package->payments()->exists()) { $package->update(['is_active'=>false]); return back()->with('warning','Used packages are retained for finance history and were disabled.'); }
        $this->commerce->deletePackage($package); return back()->with('success','Campaign tool package deleted.');
    }
}
