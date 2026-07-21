<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Http\Controllers\Controller;

class CandidateTokenPurchaseController extends Controller
{
    public function __construct(private CandidateTokenPurchaseRepositoryInterface $purchases) {}

    public function index()
    {
        $purchases = $this->purchases->paginate(request()->only(['search']));

        return view('candidate-token-purchases.index', compact('purchases'));
    }
}
