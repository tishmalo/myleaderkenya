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
use App\Http\Controllers\Admin\NewsArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

// ====================== PUBLIC ROUTES ======================
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Public News & Aspirants
Route::get('/news/public', [NewsArticleController::class, 'publicIndex'])->name('news.public');
Route::get('/news/{slug}', [NewsArticleController::class, 'publicShow'])->name('news.public.show');

Route::get('/aspirants', [CandidateController::class, 'publicIndex'])->name('aspirants.public');
Route::get('/aspirants/{candidate}', [CandidateController::class, 'publicShow'])->name('aspirants.show');

// ====================== AUTHENTICATED ROUTES ======================
Route::middleware('auth')->group(function () {

    Route::get('/smtp', [App\Http\Controllers\Admin\SmtpController::class, 'index'])->name('admin.smtp');
    Route::post('/smtp', [App\Http\Controllers\Admin\SmtpController::class, 'update'])->name('admin.smtp.update');

    // === ADMIN RESOURCES ===
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::resource('candidates', CandidateController::class);
    Route::resource('categories', CategoryController::class);

    // === EXPLICIT NEWS ROUTES (No Resource) ===
    Route::get('/news', [NewsArticleController::class, 'index'])->name('news.index');
    Route::get('/news.create', [NewsArticleController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsArticleController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit', [NewsArticleController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}', [NewsArticleController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsArticleController::class, 'destroy'])->name('news.destroy');

    Route::resource('payment-methods', PaymentMethodController::class)->names('payment-methods');
    Route::resource('donors', DonorController::class)->names('donors');
    
    // Dashboard actions were in root DashboardController, now moved to Admin\DashboardController
    Route::get('/dashboard/donors', [DashboardController::class, 'donors'])->name('dashboard.donors');

    Route::resource('tags', TagController::class)->only(['index', 'store', 'destroy']);

    // API Routes
    Route::get('/api/counties/by-bloc/{blocId}', [DashboardController::class, 'getCountiesByBloc'])->name('api.counties.by-bloc');
    Route::get('/api/constituencies/by-county', [DashboardController::class, 'getConstituenciesByCounty'])->name('api.constituencies.by-county');
    Route::get('/api/wards/by-constituency', [DashboardController::class, 'getWardsByConstituency'])->name('api.wards.by-constituency');
    Route::post('/api/stations', [DashboardController::class, 'storeStation'])->name('stations.store');

    // Dashboard & Other Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/stations', [DashboardController::class, 'stations'])->name('dashboard.stations');
    Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');

    Route::resource('/blocs', BlocController::class)->names('blocs');
    Route::resource('/counties', CountyController::class)->names('counties');
    Route::resource('/constituencies', ConstituencyController::class)->names('constituencies');
    Route::resource('/wards', WardController::class)->names('wards');

    Route::resource('users', UserController::class)->except(['show']);

    // Imports
    Route::post('/blocs/import', [BlocController::class, 'import'])->name('blocs.import');
    Route::post('/counties/import', [CountyController::class, 'import'])->name('counties.import');
    Route::post('/constituencies/import', [ConstituencyController::class, 'import'])->name('constituencies.import');
    Route::post('/wards/import', [WardController::class, 'import'])->name('wards.import');

    // Messages & Groups
    Route::get('/messages/create', [MessageController::class, 'createMessageForm'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'storeMessageFromWeb'])->name('messages.store');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');

    Route::get('/locations', [LocationController::class, 'adminIndex'])->name('locations.index');

    Route::resource('groups', GroupController::class)->only(['create', 'store', 'show']);
    Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])->name('groups.messages.store');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/stations/import', [DashboardController::class, 'importStations'])->name('stations.import');
});

require __DIR__.'/auth.php';