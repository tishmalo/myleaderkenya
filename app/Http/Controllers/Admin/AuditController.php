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

         return view('admin.audits.index',['audits'=>$this->audits->paginate($request->validated())]);

     }
    public function show(Audit $audit): View 
    { 
        $audit->load(['user','candidate']);
        return view('admin.audits.show',compact('audit')); 
    }
}
