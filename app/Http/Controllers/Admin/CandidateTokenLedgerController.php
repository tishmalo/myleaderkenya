<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenTransactionRepositoryInterface;
use App\Http\Controllers\Controller;

class CandidateTokenLedgerController extends Controller
{
    public function __construct(private CandidateTokenTransactionRepositoryInterface $transactions) {}

    public function index()
    {
        $transactions = $this->transactions->paginate(request()->only(['search', 'type']));

        return view('candidate-token-ledger.index', compact('transactions'));
    }
}
