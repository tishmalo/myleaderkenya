<?php
namespace App\Http\Controllers\Web;
use App\Contracts\Repositories\Audit\AuditRepositoryInterface; use App\Http\Controllers\Controller; use App\Http\Requests\Audit\AuditFilterRequest; use App\Models\Audit; use App\Services\Audit\AuditService; use Illuminate\View\View;
class AspirantAuditController extends Controller {
    public function __construct(private AuditRepositoryInterface $audits,private AuditService $service) {}
    public function index(AuditFilterRequest $request): View { $candidate=$request->attributes->get('audit_candidate'); return view('aspirants.audits.index',['candidate'=>$candidate,'audits'=>$this->audits->paginate($request->safe()->except(['candidate_id','actor_id']),$candidate->id),'auditService'=>$this->service]); }
    public function show(AuditFilterRequest $request,Audit $audit): View { $candidate=$request->attributes->get('audit_candidate'); $record=$this->audits->findForCandidate($audit->id,$candidate->id); abort_unless($record,404); return view('aspirants.audits.show',['candidate'=>$candidate,'audit'=>$record,'auditService'=>$this->service]); }
}
