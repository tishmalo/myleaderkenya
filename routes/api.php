<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\LocationController;

// ====================== PUBLIC ROUTES (No Login Required) ======================

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refresh']);

// ====================== PUBLIC LOCATION HIERARCHY (Cascading Dropdowns) ======================
Route::get('/counties', [MessageController::class, 'getCounties']);
Route::get('/constituencies/by-county', [MessageController::class, 'getConstituenciesByCounty']);
Route::get('/wards/by-constituency', [MessageController::class, 'getWardsByConstituency']);


// Other Public Routes
Route::get('/tags', [MessageController::class, 'getTags']);
Route::post('/nearby_messages', [MessageController::class, 'nearbyMessages']);
Route::get('/constituency_messages', [MessageController::class, 'getConstituencyMessages']);
Route::get('/get_locations', [LocationController::class, 'getLocations']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('/check-otp', [AuthController::class, 'checkOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Payment Methods - MUST BE PUBLIC so users can see donation options
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

// Live Stats (Public)
Route::get('/stats/live', function () {
    $byCounty = \App\Models\User::whereNotNull('county')
        ->selectRaw('county, count(*) as total')
        ->groupBy('county')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    return response()->json([
        'confirmedVoters' => \App\Models\User::where('voter_registered', true)->count(),
        'totalMessages'   => \App\Models\Message::count(),
        'stationsCount'   => \App\Models\Station::count() ?? 0,
        'avgAge'          => round(\App\Models\User::whereNotNull('dob')
                            ->avg(DB::raw('TIMESTAMPDIFF(YEAR, dob, CURDATE())')) ?? 0),
        'totalUsers'      => \App\Models\User::count(),
        'totalRegistered' => \App\Models\User::where('is_registered', true)->count(),
        'maleRegistered'  => \App\Models\User::where('gender', 'male')->where('is_registered', true)->count(),
        'femaleRegistered'=> \App\Models\User::where('gender', 'female')->where('is_registered', true)->count(),
        'countyLabels'    => $byCounty->pluck('county'),
        'countyData'      => $byCounty->pluck('total'),
        'genderData'      => [
            \App\Models\User::where('gender', 'male')->count(),
            \App\Models\User::where('gender', 'female')->count(),
            \App\Models\User::whereNotIn('gender', ['male','female'])->orWhereNull('gender')->count(),
        ],
    ]);
});

// ====================== PROTECTED ROUTES (Require Authentication) ======================
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/save-player-id', [AuthController::class, 'savePlayerId']);

    // Messages
    Route::post('/send_message', [MessageController::class, 'sendMessage']);
    Route::post('/messages/{message_id}/react', [MessageController::class, 'reactToMessage']);
    Route::post('/send-location-message', [MessageController::class, 'sendLocationMessage']);
    Route::get('/location-messages', [MessageController::class, 'getLocationMessages']);

    // Voter Status
    Route::get('/voter_status', [MessageController::class, 'getVoterStatus']);
    Route::post('/voter_status', [MessageController::class, 'updateVoterStatus']);

    // Groups
    Route::post('/groups/create', [MessageController::class, 'createGroup']);
    Route::post('/groups/join', [MessageController::class, 'joinGroup']);
    Route::post('/groups/send-message', [MessageController::class, 'sendGroupMessage']);
    Route::get('/groups/my-groups', [MessageController::class, 'getMyGroups']);
    Route::get('/groups/{group_id}/messages', [MessageController::class, 'getGroupMessages']);

    // Location
    Route::post('/upload_location', [LocationController::class, 'upload']);

    // Donors
    Route::get('/donors', [\App\Http\Controllers\Api\DonorController::class, 'index']);
    Route::post('/donors', [\App\Http\Controllers\Api\DonorController::class, 'store']);
});