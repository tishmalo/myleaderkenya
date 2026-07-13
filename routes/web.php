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
use App\Http\Controllers\Admin\PoliticalPartyController;
use App\Http\Controllers\Admin\CoalitionController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CampaignToolController;
use App\Http\Controllers\Admin\NewsArticleController;
use App\Http\Controllers\Admin\FrontendPageController as AdminFrontendPageController;
use App\Http\Controllers\Admin\LiveStatFigureController;
use App\Http\Controllers\Web\FrontendPageController as PublicFrontendPageController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\AspirantRegistrationController;
use App\Http\Controllers\Web\AspirantDashboardController;
use App\Http\Controllers\Web\AspirantToolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// Candidate location JSON helpers used by the admin candidate form.
Route::get('/api/counties', function () {
    return \App\Models\County::query()
        ->orderBy('name')
        ->get(['id', 'name']);
});

Route::get('/api/constituencies', function (\Illuminate\Http\Request $request) {
    return \App\Models\Constituency::query()
        ->when($request->query('county_id'), fn ($query, $countyId) => $query->where('county_id', $countyId))
        ->orderBy('name')
        ->get(['id', 'name', 'county_id']);
});

Route::get('/api/wards', function (\Illuminate\Http\Request $request) {
    return \App\Models\Ward::query()
        ->when($request->query('constituency_id'), fn ($query, $constituencyId) => $query->where('constituency_id', $constituencyId))
        ->get(['id', 'name', 'constituency_id'])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();
});

// ====================== PUBLIC ROUTES (Throttled) ======================
Route::middleware('throttle:web')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/featured-aspirants', [LandingController::class, 'featuredAspirants'])->name('landing.featured-aspirants');
    Route::get('/about-us', [PublicFrontendPageController::class, 'about'])->name('about.public');
    Route::get('/live-stats', [PublicFrontendPageController::class, 'liveStats'])->name('live-stats.public');
    Route::get('/download-app', [PublicFrontendPageController::class, 'downloadApp'])->name('download-app.public');
    Route::get('/contact-us', [PublicFrontendPageController::class, 'contact'])->name('contact.public');
    
    Route::get('/privacy', function () {
        return view('privacy');
    })->name('privacy');

    // Public Campaign Tools, News & Aspirants
    Route::get('/campaign-tools', [CampaignToolController::class, 'publicIndex'])->name('campaign-tools.public');
    Route::get('/campaign-tools/{slug}', [CampaignToolController::class, 'publicShow'])->name('campaign-tools.show');

    Route::get('/parties', [PoliticalPartyController::class, 'publicIndex'])->name('parties.public');
    Route::get('/parties/{slug}', [PoliticalPartyController::class, 'publicShow'])->name('parties.show');
    Route::get('/coalitions', [CoalitionController::class, 'publicIndex'])->name('coalitions.public');
    Route::get('/coalitions/{slug}', [CoalitionController::class, 'publicShow'])->name('coalitions.show');

    Route::get('/news/public', [NewsArticleController::class, 'publicIndex'])->name('news.public');
    Route::get('/news/{slug}', [NewsArticleController::class, 'publicShow'])->name('news.public.show');
    
    Route::get('/aspirants/register', [AspirantRegistrationController::class, 'create'])->name('aspirants.register');
    Route::post('/aspirants/register', [AspirantRegistrationController::class, 'store'])->name('aspirants.register.store');
    Route::get('/aspirants', [CandidateController::class, 'publicIndex'])->name('aspirants.public');
    Route::get('/aspirants/{candidate}', [CandidateController::class, 'publicShow'])->name('aspirants.show');
});


// ====================== AUTHENTICATED ROUTES ======================
Route::middleware('auth')->group(function () {
    Route::get('/aspirant/dashboard', AspirantDashboardController::class)->name('aspirant.dashboard');
    Route::get('/aspirant/tools/{key}', [AspirantToolController::class, 'show'])->name('aspirant.tools.show');
    Route::post('/aspirant/tools/opinion-polls/polls', [AspirantToolController::class, 'storePoll'])->name('aspirant.tools.polls.store');

    // --- Core Admin & Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/stations', [DashboardController::class, 'stations'])->name('dashboard.stations');
    Route::post('/stations', [DashboardController::class, 'storeStation'])->name('stations.store');
    Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');
    Route::get('/dashboard/donors', [DashboardController::class, 'donors'])->name('dashboard.donors');
    Route::get('/live-stat-figures', [LiveStatFigureController::class, 'index'])->name('live-stat-figures.index');
    Route::post('/live-stat-figures', [LiveStatFigureController::class, 'store'])->name('live-stat-figures.store');
    Route::delete('/live-stat-figures/batches/{batchId}', [LiveStatFigureController::class, 'destroyBatch'])->name('live-stat-figures.batches.destroy');
    Route::delete('/live-stat-figures/{liveStatFigure}', [LiveStatFigureController::class, 'destroy'])->name('live-stat-figures.destroy');
    
    Route::get('/smtp', [SmtpController::class, 'index'])->name('admin.smtp');
    Route::post('/smtp', [SmtpController::class, 'update'])->name('admin.smtp.update');

    // --- Content Management ---
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::get('/candidates/search', [CandidateController::class, 'search'])->name('candidates.search');
    Route::patch('/candidates/{candidate}/featured', [CandidateController::class, 'toggleFeatured'])->name('candidates.featured');
    Route::patch('/candidates/{candidate}/approval', [CandidateController::class, 'updateApproval'])->name('candidates.approval');
    Route::resource('candidates', CandidateController::class);
    Route::resource('tags', TagController::class)->only(['index', 'store', 'destroy']);
    Route::resource('/admin/political-parties', PoliticalPartyController::class)
        ->parameters(['political-parties' => 'politicalParty'])
        ->names('political-parties')
        ->except(['show']);
    Route::resource('/admin/coalitions', CoalitionController::class)
        ->parameters(['coalitions' => 'coalition'])
        ->names('coalitions')
        ->except(['show']);
    
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
    Route::get('/admin/frontend-pages', [AdminFrontendPageController::class, 'index'])->name('frontend-pages.index');
    Route::get('/admin/frontend-pages/{page}/edit', [AdminFrontendPageController::class, 'edit'])->name('frontend-pages.edit');
    Route::put('/admin/frontend-pages/{page}', [AdminFrontendPageController::class, 'update'])->name('frontend-pages.update');

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
