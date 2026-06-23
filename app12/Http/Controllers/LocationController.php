<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Message;   

class LocationController extends Controller
{
    // ====================== PUBLIC API ======================

    /**
     * GET /api/get_locations  (Public)
     */
    public function getLocations()
    {
        $locations = Location::select('name', 'latitude', 'longitude')->get();
        return response()->json($locations);
    }

    // ====================== PROTECTED API (requires token) ======================

    /**
     * POST /api/upload_location  (Protected - needs Bearer token)
     */
    public function upload(Request $request)
    {
        $user = $request->user();   // Sanctum authenticated user

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Location::updateOrCreate(
            ['name' => $user->username],
            [
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        return response()->json([
            'message'  => 'Location updated successfully',
            'username' => $user->username
        ], 200);
    }

    // ====================== ADMIN DASHBOARDS ======================

    /**
     * GET /locations  (Old dashboard - shows raw locations)
     */
    public function adminIndex()
    {
        $locations = Location::all();
        return view('locations.index', compact('locations'));
    }

    /**
     * GET /users  (New dashboard - shows registered users + locations)
     */
    public function adminUsers()
{
    $users = User::with('location')           // eager load location relationship
                 ->latest()
                 ->paginate(20);              // ← Change to paginate()

    return view('users.index', compact('users'));
}
    // public function adminUsers()
    // {
    //     $users = User::with('location')->get();
    //     return view('users.index', compact('users'));
    // }

    
public function dashboard()
{
    $users = User::with('location')->get();
    $messages = Message::latest()->take(50)->get();
    
    $totalVoters = User::where('is_voter', true)->count();
    $confirmedVoters = User::where('is_voter', true)->count();
    $avgAge = User::whereNotNull('age')->avg('age') ?? 0;

    // Fetch locations for the Locations tab
    $locations = Location::all();   // ← ADD THIS LINE

    return view('dashboard', compact(
        'users', 
        'messages', 
        'totalVoters', 
        'confirmedVoters', 
        'avgAge',
        'locations'           // ← Pass this variable
    ));
}
}