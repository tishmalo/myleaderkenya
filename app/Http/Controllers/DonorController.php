<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController extends Controller
{
        public function index()
    {
        $donors = Donor::latest()->paginate(15);

        // Calculate stats for the cards
        $totalDonors = Donor::count();
        $totalAmount = Donor::where('status', 'completed')->sum('amount');
        $recentDonors = Donor::latest()->take(8)->get();   // For the recent table

        return view('donors.index', compact(
            'donors',
            'totalDonors',
            'totalAmount',
            'recentDonors'
        ));
    }
    // public function index()
    // {
    //     $donors = Donor::latest()->paginate(15);
    //     return view('donors.index', compact('donors'));
    // }

    public function create()
    {
        return view('donors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'payment_method'  => 'required|in:mpesa,bank_transfer,paypal,cash,other',
            'amount'          => 'required|numeric|min:0.01',
            'currency'        => 'nullable|string|max:10',
            'details'         => 'nullable|string',
            'status'          => 'required|in:pending,completed,failed,refunded',
            'payment_details' => 'nullable|array',
        ]);

        Donor::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'payment_method'  => $request->payment_method,
            'amount'          => $request->amount,
            'currency'        => $request->currency ?? 'KES',
            'details'         => $request->details,
            'status'          => $request->status,
            'payment_details' => $request->payment_details ?? null,
            'user_id'         => auth()->id(),
        ]);

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record created successfully.');
    }

    public function edit(Donor $donor)
    {
        return view('donors.edit', compact('donor'));
    }

    public function update(Request $request, Donor $donor)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'payment_method'  => 'required|in:mpesa,bank_transfer,paypal,cash,other',
            'amount'          => 'required|numeric|min:0.01',
            'currency'        => 'nullable|string|max:10',
            'details'         => 'nullable|string',
            'status'          => 'required|in:pending,completed,failed,refunded',
            'payment_details' => 'nullable|array',
        ]);

        $donor->update([
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'payment_method'  => $request->payment_method,
            'amount'          => $request->amount,
            'currency'        => $request->currency ?? 'KES',
            'details'         => $request->details,
            'status'          => $request->status,
        ]);

        if ($request->has('payment_details')) {
            $donor->update(['payment_details' => $request->payment_details]);
        }

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record updated successfully.');
    }

    public function destroy(Donor $donor)
    {
        $donor->delete();

        return redirect()->route('donors.index')
                         ->with('success', 'Donor record deleted successfully.');
    }
}