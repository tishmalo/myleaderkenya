<?php

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BlocController;
use App\Http\Controllers\Admin\CountyController;
use App\Http\Controllers\Admin\ConstituencyController;
use App\Http\Controllers\Admin\WardController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CampaignToolController;
use App\Http\Controllers\Admin\NewsArticleController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================== PUBLIC ROUTES (Throttled) ======================
Route::middleware('throttle:web')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    
    Route::get('/privacy', function () {
        return view('privacy');
    })->name('privacy');

    // Public Campaign Tools, News & Aspirants
    Route::get('/campaign-tools', [CampaignToolController::class, 'publicIndex'])->name('campaign-tools.public');
    Route::get('/campaign-tools/{slug}', [CampaignToolController::class, 'publicShow'])->name('campaign-tools.show');

    Route::get('/news/public', [NewsArticleController::class, 'publicIndex'])->name('news.public');
    Route::get('/news/{slug}', [NewsArticleController::class, 'publicShow'])->name('news.public.show');
    
    Route::get('/aspirants', [CandidateController::class, 'publicIndex'])->name('aspirants.public');
    Route::get('/aspirants/{candidate}', [CandidateController::class, 'publicShow'])->name('aspirants.show');
});


// ====================== AUTHENTICATED ROUTES ======================
Route::middleware('auth')->group(function () {

    // --- Core Admin & Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/stations', [DashboardController::class, 'stations'])->name('dashboard.stations');
    Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');
    Route::get('/dashboard/donors', [DashboardController::class, 'donors'])->name('dashboard.donors');
    
    Route::get('/smtp', [SmtpController::class, 'index'])->name('admin.smtp');
    Route::post('/smtp', [SmtpController::class, 'update'])->name('admin.smtp.update');

    // --- Content Management ---
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::resource('candidates', CandidateController::class);
    Route::resource('tags', TagController::class)->only(['index', 'store', 'destroy']);
    
    Route::get('/news', [NewsArticleController::class, 'index'])->name('news.index');
    Route::get('/news.create', [NewsArticleController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsArticleController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit', [NewsArticleController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}', [NewsArticleController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsArticleController::class, 'destroy'])->name('news.destroy');

    Route::resource('/admin/campaign-tools', CampaignToolController::class)
        ->parameters(['campaign-tools' => 'campaignTool'])
        ->names('campaign-tools')
        ->except(['show']);

    // --- Finance & Donors ---
    Route::resource('payment-methods', PaymentMethodController::class)->names('payment-methods');
    Route::resource('donors', DonorController::class)->names('donors');

    // --- Geography (Core Data) ---
    Route::resource('/blocs', BlocController::class)->names('blocs');
    Route::resource('/counties', CountyController::class)->names('counties');
    Route::resource('/constituencies', ConstituencyController::class)->names('constituencies');
    Route::resource('/wards', WardController::class)->names('wards');
    Route::get('/locations', [LocationController::class, 'adminIndex'])->name('locations.index');

    // Geography Imports
    Route::post('/blocs/import', [BlocController::class, 'import'])->name('blocs.import');
    Route::post('/counties/import', [CountyController::class, 'import'])->name('counties.import');
    Route::post('/constituencies/import', [ConstituencyController::class, 'import'])->name('constituencies.import');
    Route::post('/wards/import', [WardController::class, 'import'])->name('wards.import');
    Route::post('/stations/import', [DashboardController::class, 'importStations'])->name('stations.import');

    // --- User & Group Management ---
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('groups', GroupController::class)->only(['create', 'store', 'show']);
    Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])->name('groups.messages.store');

    // --- Messages Management ---
    Route::get('/messages/create', [MessageController::class, 'createMessageForm'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'storeMessageFromWeb'])->name('messages.store');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
