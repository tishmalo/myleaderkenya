<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function index()
{
    // County Chart Data
    $countyData = \App\Models\User::select('county')
        ->selectRaw('COUNT(*) as count')
        ->where('is_voter', true)
        ->groupBy('county')
        ->orderByDesc('count')
        ->get();

    // Gender Chart Data
    $genderData = \App\Models\User::select('gender')
        ->selectRaw('COUNT(*) as count')
        ->where('is_voter', true)
        ->groupBy('gender')
        ->get();

    return view('landing', compact('countyData', 'genderData'));
}
}
