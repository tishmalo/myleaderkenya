<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TokenPurchaseFilterRequest;

class CandidateTokenPurchaseController extends Controller
{
    public function __construct(private CandidateTokenPurchaseRepositoryInterface $purchases) {}

    public function index(TokenPurchaseFilterRequest $request)
    {
        $filters = $request->validated();
        $tab = $filters['tab'] ?? 'candidate';
        $purchases = $kittyPurchases = $donations = null;

        if ($tab === 'kitty') {
            $kittyPurchases = $this->purchases->paginateKittyPurchases($filters);
        } elseif ($tab === 'donations') {
            $donations = $this->purchases->paginateAspirantDonations($filters);
        } else {
            $purchases = $this->purchases->paginate($filters);
        }

        return view('candidate-token-purchases.index', compact('tab', 'purchases', 'kittyPurchases', 'donations'));
    }
}
