<?php
namespace App\Http\Controllers\Admin;
use App\Contracts\Repositories\Web\PublicPulseMentionRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PublicPulseController extends Controller
{
    public function __construct(private PublicPulseMentionRepositoryInterface $mentionRepository) {}
    public function index(Request $request): View
    {
        $filters = $request->only(['language','tone','sentiment','topic','low_confidence','search']);
        return view('public-pulse.legacy', ['mentions'=>$this->mentionRepository->paginateForAdmin($filters), 'filters'=>$filters]);
    }
}
