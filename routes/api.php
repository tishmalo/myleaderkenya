<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\DonorController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\CoalitionController as ApiCoalitionController;
use App\Http\Controllers\Api\PoliticalPartyController as ApiPoliticalPartyController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\AspirantController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ====================== PUBLIC ROUTES ======================

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-verification', [AuthController::class, 'resendEmailVerification']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/check-otp', [AuthController::class, 'checkOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Location Hierarchy (Public API)
Route::prefix('locations')->group(function () {
    Route::get('/counties', [LocationController::class, 'getCounties']);
    Route::get('/constituencies/by-county', [LocationController::class, 'getConstituenciesByCounty']);
    Route::get('/wards/by-constituency', [LocationController::class, 'getWardsByConstituency']);
    Route::get('/all', [LocationController::class, 'getLocations']);
});

// Admin-Specific API (Consolidated from web.php)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/counties/by-bloc/{blocId}', [DashboardController::class, 'getCountiesByBloc'])->name('api.counties.by-bloc');
    Route::get('/constituencies/by-county', [DashboardController::class, 'getConstituenciesByCounty'])->name('api.constituencies.by-county');
    Route::get('/wards/by-constituency', [DashboardController::class, 'getWardsByConstituency'])->name('api.wards.by-constituency');
    Route::post('/stations', [DashboardController::class, 'storeStation'])->name('api.stations.store');
});

// Messaging & Content
Route::get('/tags', [MessageController::class, 'getTags']);
Route::post('/nearby_messages', [MessageController::class, 'nearbyMessages']);
Route::get('/constituency_messages', [MessageController::class, 'getConstituencyMessages']);


// Public Content APIs
Route::get('/news', [NewsController::class, 'list']);
Route::get('/news/{slug}', [NewsController::class, 'show']);
Route::get('/parties', [ApiPoliticalPartyController::class, 'list']);
Route::get('/parties/{slug}', [ApiPoliticalPartyController::class, 'show']);
Route::get('/political-parties', [ApiPoliticalPartyController::class, 'list']);
Route::get('/political-parties/{slug}', [ApiPoliticalPartyController::class, 'show']);
Route::get('/coalitions', [ApiCoalitionController::class, 'list']);
Route::get('/coalitions/{slug}', [ApiCoalitionController::class, 'show']);
Route::get('/aspirants', [AspirantController::class, 'list']);
Route::get('/aspirants/{candidate}', [AspirantController::class, 'show']);
// Donations
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

// Stats
Route::get('/stats/live', [StatsController::class, 'liveStats']);


// ====================== PROTECTED ROUTES ======================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/refresh-token', [AuthController::class, 'refresh']);

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'profile']);
        Route::post('/', [AuthController::class, 'updateProfile']);
        Route::post('/save-player-id', [AuthController::class, 'savePlayerId']);
    });

    // Messages
    Route::prefix('messages')->group(function () {
        Route::post('/send', [MessageController::class, 'sendMessage']);
        Route::post('/{message_id}/react', [MessageController::class, 'reactToMessage']);
        Route::post('/location-message', [MessageController::class, 'sendLocationMessage']);
        Route::get('/location-messages', [MessageController::class, 'getLocationMessages']);
    });

    // Voter Status
    Route::prefix('voter-status')->group(function () {
        Route::get('/', [MessageController::class, 'getVoterStatus']);
        Route::post('/', [MessageController::class, 'updateVoterStatus']);
    });

    // Groups
    Route::prefix('groups')->group(function () {
        Route::post('/create', [MessageController::class, 'createGroup']);
        Route::post('/join', [MessageController::class, 'joinGroup']);
        Route::post('/send-message', [MessageController::class, 'sendGroupMessage']);
        Route::get('/my-groups', [MessageController::class, 'getMyGroups']);
        Route::get('/{group_id}/messages', [MessageController::class, 'getGroupMessages']);
    });

    // Tracking/Upload
    Route::post('/upload-location', [LocationController::class, 'upload']);

    // Donors
    Route::prefix('donors')->group(function () {
        Route::get('/', [DonorController::class, 'index']);
        Route::post('/', [DonorController::class, 'store']);
    });
});
