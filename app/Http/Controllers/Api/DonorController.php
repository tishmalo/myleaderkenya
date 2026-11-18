<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDonorRequest;
use App\Models\Donor;

class DonorController extends Controller
{
    public function index()
    {
        $donors = Donor::latest()->paginate(15);
        return response()->json($donors);
    }

    public function store(StoreDonorRequest $request)
    {
        $donor = Donor::create($request->validated());

        return response()->json([
            'message' => 'Donor created successfully',
            'data'    => $donor
        ], 201);
    }
}