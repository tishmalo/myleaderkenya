<?php
namespace App\Http\Controllers\Admin;
use App\Contracts\Repositories\Audit\AuditRepositoryInterface; 
use App\Http\Controllers\Controller; 
use App\Http\Requests\Audit\AuditFilterRequest; 
use App\Models\Audit; 
use Illuminate\View\View;
class AuditController extends Controller {
    public function __construct(private AuditRepositoryInterface $audits) {

    }
    public function index(AuditFilterRequest $request): View 
    {
         $filters = $request->validated();

         // The contributor card keeps its own range so the audit list above can
         // stay on a different period.
         $contributorFilters = array_filter([
             'actor' => $filters['contributor_actor'] ?? null,
             'date_from' => $filters['contributor_from'] ?? null,
             'date_to' => $filters['contributor_to'] ?? null,
         ]);

         $candidateCreatorSummary = $this->audits->candidateCreatorSummary($contributorFilters);

         return view('admin.audits.index',[
             'audits' => $this->audits->paginate($filters),
             'candidateCreatorSummary' => $candidateCreatorSummary,
             'contributorFilters' => $contributorFilters,
             'contributorTotals' => [
                 'contributors' => $candidateCreatorSummary->count(),
                 'live' => (int) $candidateCreatorSummary->sum('live_candidates'),
                 'deleted' => (int) $candidateCreatorSummary->sum('deleted_candidates'),
                 'links' => (int) $candidateCreatorSummary->sum('links_added'),
             ],
         ]);

     }
    public function show(Audit $audit): View 
    { 
        $audit->load(['user','candidate']);
        return view('admin.audits.show',compact('audit')); 
    }
}
