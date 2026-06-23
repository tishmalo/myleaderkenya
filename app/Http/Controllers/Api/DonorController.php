<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    public function index()
    {
        $donors = Donor::latest()->paginate(15);
        return response()->json($donors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'payment_method'  => 'required|string|in:mpesa,bank_transfer,paypal,cash,other',
            'amount'          => 'required|numeric|min:1',
            'details'         => 'nullable|string',
            'payment_details' => 'nullable|array',
            'status'          => 'required|in:pending,completed,failed,refunded',
        ]);

        $donor = Donor::create($request->all());

        return response()->json([
            'message' => 'Donor created successfully',
            'data'    => $donor
        ], 201);
    }
}