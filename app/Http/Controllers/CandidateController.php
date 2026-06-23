<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use App\Http\Controllers\Bloc;
use App\Models\Bloc;  

class CandidateController extends Controller
{
    public function create()
    {
        $positions = Position::orderBy('name')->get();
        $blocs = Bloc::orderBy('name')->get();
        return view('candidates.create', compact('positions', 'blocs'));
    }

    public function edit(Candidate $candidate)
    {
        $positions = Position::orderBy('name')->get();
        $blocs = Bloc::orderBy('name')->get();
        return view('candidates.edit', compact('candidate', 'positions', 'blocs'));
    }


    public function index()
    {
        $candidates = Candidate::with('position')->latest()->paginate(15);
        return view('candidates.index', compact('candidates'));
    }

    // public function create()
    // {
    //     $positions = Position::orderBy('name')->get();
    //     return view('candidates.create', compact('positions'));
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'          => 'required|string|max:255',
    //         'nick_name'     => 'nullable|string|max:100',
    //         'phone'         => 'nullable|string|max:20',
    //         'email'         => 'nullable|email|max:255',
    //         'position_id'   => 'required|exists:positions,id',
    //         'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'about'         => 'nullable|string',
    //         'county'        => 'nullable|string',
    //         'constituency'  => 'nullable|string',
    //         'ward'          => 'nullable|string',
    //     ]);

    //     $data = $request->except('profile_picture');

    //     if ($request->hasFile('profile_picture')) {
    //         $data['profile_picture'] = $request->file('profile_picture')
    //             ->store('candidates', 'public');
    //     }

    //     Candidate::create($data);

    //     return redirect()->route('candidates.index')
    //                      ->with('success', 'Candidate added successfully!');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nick_name'     => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'position_id'   => 'required|exists:positions,id',
            'bloc_id'       => 'nullable|exists:blocs,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'about'         => 'nullable|string',
            'county'        => 'nullable|string',
            'constituency'  => 'nullable|string',
            'ward'          => 'nullable|string',
        ]);

        $data = $request->except('profile_picture');

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('candidates', 'public');
        }

        Candidate::create($data);

        return redirect()->route('candidates.index')
                         ->with('success', 'Candidate added successfully!');
    }

    // public function edit(Candidate $candidate)
    // {
    //     $positions = Position::orderBy('name')->get();
    //     return view('candidates.edit', compact('candidate', 'positions'));
    // }

    public function update(Request $request, Candidate $candidate)
    {
        // Same validation as store...
        $request->validate([
            'name'          => 'required|string|max:255',
            'nick_name'     => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'position_id'   => 'required|exists:positions,id',
            'bloc_id'       => 'nullable|exists:blocs,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'about'         => 'nullable|string',
            'county'        => 'nullable|string',
            'constituency'  => 'nullable|string',
            'ward'          => 'nullable|string',
        ]);

        $data = $request->except('profile_picture');

        if ($request->hasFile('profile_picture')) {
            if ($candidate->profile_picture) Storage::disk('public')->delete($candidate->profile_picture);
            $data['profile_picture'] = $request->file('profile_picture')->store('candidates', 'public');
        }

        $candidate->update($data);

        return redirect()->route('candidates.index')
                         ->with('success', 'Candidate updated successfully!');
    }

    // public function update(Request $request, Candidate $candidate)
    // {
    //     $request->validate([
    //         'name'          => 'required|string|max:255',
    //         'nick_name'     => 'nullable|string|max:100',
    //         'phone'         => 'nullable|string|max:20',
    //         'email'         => 'nullable|email|max:255',
    //         'position_id'   => 'required|exists:positions,id',
    //         'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'about'         => 'nullable|string',
    //         'county'        => 'nullable|string',
    //         'constituency'  => 'nullable|string',
    //         'ward'          => 'nullable|string',
    //     ]);

    //     $data = $request->except('profile_picture');

    //     if ($request->hasFile('profile_picture')) {
    //         if ($candidate->profile_picture) {
    //             Storage::disk('public')->delete($candidate->profile_picture);
    //         }
    //         $data['profile_picture'] = $request->file('profile_picture')
    //             ->store('candidates', 'public');
    //     }

    //     $candidate->update($data);

    //     return redirect()->route('candidates.index')
    //                      ->with('success', 'Candidate updated successfully!');
    // }

    public function destroy(Candidate $candidate)
    {
        if ($candidate->profile_picture) {
            Storage::disk('public')->delete($candidate->profile_picture);
        }
        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully.'
        ]);
    }

    // Add these methods to CandidateController

public function publicIndex()
{
    $query = Candidate::with('position', 'bloc');

    // Search by name or nick name
    if ($search = request('search')) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('nick_name', 'like', "%{$search}%");
    }

    // Filter by County
    if ($county = request('county')) {
        $query->where('county', $county);
    }

    // Filter by Position
    if ($positionId = request('position')) {
        $query->where('position_id', $positionId);
    }

    $candidates = $query->latest()->paginate(16);

    $positions = \App\Models\Position::orderBy('name')->get();
    // $counties = County::orderBy('name')->get();
        // return view('constituencies.create', compact('counties'));
    $counties  = Candidate::whereNotNull('county')->distinct()->pluck('county');

    return view('aspirants.public.index', compact('candidates', 'positions', 'counties'));
}

public function publicShow(Candidate $candidate)
{
    $candidate->load('position', 'bloc');

    // Related articles where this candidate is tagged
    $relatedArticles = \App\Models\NewsArticle::with('categories')
                        ->whereHas('candidates', fn($q) => $q->where('candidates.id', $candidate->id))
                        ->where('status', 'published')
                        ->latest()
                        ->take(6)
                        ->get();

    return view('aspirants.public.show', compact('candidate', 'relatedArticles'));
}
}
