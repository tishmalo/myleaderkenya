<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\Admin\CandidateTokenTransactionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TokenLedgerFilterRequest;

class CandidateTokenLedgerController extends Controller
{
    public function __construct(private CandidateTokenTransactionRepositoryInterface $transactions) {}

    public function index(TokenLedgerFilterRequest $request)
    {
        $transactions = $this->transactions->paginate($request->validated());

        return view('candidate-token-ledger.index', compact('transactions'));
    }
}
