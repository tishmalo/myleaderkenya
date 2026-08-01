<?php
namespace App\Http\Controllers\Admin;
use App\Contracts\Repositories\Web\PublicPulseJobRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublicPulseTweetFilterRequest;
use App\Http\Requests\Admin\StorePublicPulseJobRequest;
use App\Models\PublicPulseJob;
use App\Services\PublicPulse\PublicPulseJobSubmissionService;
use App\Services\PublicPulse\PublicPulseJobDetailService;
use App\Services\PublicPulse\PublicPulseJobSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PublicPulseJobController extends Controller
{
    public function __construct(private PublicPulseJobRepositoryInterface $jobs, private PublicPulseJobSubmissionService $submission, private PublicPulseJobSyncService $sync, private PublicPulseJobDetailService $details) {}
    public function index(Request $request): View
    {
        $filters = $request->only(['candidate_id','status','date_from','date_to']);
        return view('public-pulse.index', [
            'jobs' => $this->jobs->paginateForAdmin($filters),
            'filters' => $filters,
            'submissionCandidate' => $this->jobs->candidateOption((int) old('candidate_id')),
            'filterCandidate' => $this->jobs->candidateOption(isset($filters['candidate_id']) ? (int) $filters['candidate_id'] : null),
        ]);
    }
    public function store(StorePublicPulseJobRequest $request): RedirectResponse
    {
        $job = $this->submission->submit($request->validated(), $request->user()?->id);
        return redirect()->route('public-pulse.jobs.show', $job)->with($job->engine_job_id ? 'success' : 'error', $job->engine_job_id ? 'Pulse job submitted.' : 'The job was saved but the engine submission failed. You can retry it.');
    }
    public function show(PublicPulseTweetFilterRequest $request, PublicPulseJob $publicPulseJob): View
    {
        $tweets = $this->details->tweets($publicPulseJob, $request->validated());
        return view('public-pulse.show', ['job'=>$publicPulseJob->load(['candidate','submitter']), 'tweets'=>$tweets, 'filters'=>$request->validated()]);
    }
    public function sync(PublicPulseJob $publicPulseJob): RedirectResponse
    {
        $this->sync->sync($publicPulseJob);
        return back()->with('success', 'Pulse job refreshed.');
    }
    public function retry(PublicPulseJob $publicPulseJob): RedirectResponse
    {
        $this->submission->retry($publicPulseJob);
        return back()->with('success', 'Pulse job submission retried.');
    }
}




