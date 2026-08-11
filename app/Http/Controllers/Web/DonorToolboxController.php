<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ToolboxPurchaseRequest;
use App\Http\Requests\Web\PayAdoptionSponsorshipRequest;
use App\Models\CampaignToolRequest;
use App\Services\Web\DonorToolboxService;
use App\Services\Web\AspirantSupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Throwable;

class DonorToolboxController extends Controller
{
    public function __construct(private DonorToolboxService $toolbox, private AspirantSupportService $supports, private CandidateTokenPackageRepositoryInterface $packages) {}

    public function index(Request $request): View
    {
        return view('account.toolbox.index', array_merge($this->toolbox->data($request->user()), $this->supports->dataForSupporter($request->user())));
    }

    public function purchase(ToolboxPurchaseRequest $request): RedirectResponse
    {
        $package=$this->packages->findActive((int)$request->validated('candidate_token_package_id'));
        try {
            $url = $request->validated('objective') === 'support_aspirant'
                ? $this->supports->start($request->user(), $package, $request->validated())
                : $this->toolbox->startPurchase($request->user(), $package, $request->validated());
            return redirect()->away($url);
        }
        catch (Throwable $e) { return back()->with('warning',$e->getMessage()); }
    }

    public function pay(PayAdoptionSponsorshipRequest $request, CampaignToolRequest $campaignToolRequest): RedirectResponse
    {
        try {
            $this->toolbox->payAdoption($request->user(), $campaignToolRequest->id, (int) $request->validated('token_amount'));
            return back()->with('success', 'Sponsorship paid successfully from your Toolbox.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            return back()->withErrors(['payment' => $e->getMessage()])->withInput();
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        try { $result=$this->toolbox->completePurchase($request->query()); }
        catch (Throwable $e) { $result=['status'=>'failed','message'=>$e->getMessage()]; }
        return redirect()->route('account.toolbox.index')->with($result['status']==='success'?'success':'warning',$result['message']);
    }

    public function supportCallback(Request $request): RedirectResponse
    {
        try { $result = $this->supports->complete($request->query()); }
        catch (Throwable $e) { $result = ['status' => 'failed', 'message' => $e->getMessage()]; }
        return redirect()->route('account.toolbox.index', ['tab' => 'direct-support'])
            ->with($result['status'] === 'success' ? 'success' : 'warning', $result['message']);
    }
}
