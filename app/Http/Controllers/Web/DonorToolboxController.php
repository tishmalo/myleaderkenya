<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TokenPurchaseRequest;
use App\Http\Requests\Web\PayAdoptionSponsorshipRequest;
use App\Models\CampaignToolRequest;
use App\Services\Web\DonorToolboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Throwable;

class DonorToolboxController extends Controller
{
    public function __construct(private DonorToolboxService $toolbox, private CandidateTokenPackageRepositoryInterface $packages) {}

    public function index(Request $request): View
    {
        return view('account.toolbox.index', $this->toolbox->data($request->user()));
    }

    public function purchase(TokenPurchaseRequest $request): RedirectResponse
    {
        $package=$this->packages->findActive((int)$request->validated('candidate_token_package_id'));
        try { return redirect()->away($this->toolbox->startPurchase($request->user(),$package,$request->validated())); }
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
}