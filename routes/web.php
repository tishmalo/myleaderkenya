<?php

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\UserAccessController;
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
use App\Http\Controllers\Admin\CampaignWebsiteRequestController;
use App\Http\Controllers\Admin\CampaignWebsiteSampleController;
use App\Http\Controllers\Admin\NewsArticleController;
use App\Http\Controllers\Admin\FrontendPageController as AdminFrontendPageController;
use App\Http\Controllers\Admin\LiveStatFigureController;
use App\Http\Controllers\Web\FrontendPageController as PublicFrontendPageController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\AspirantRegistrationController;
use App\Http\Controllers\Web\CandidateClaimController;
use App\Http\Controllers\Web\AspirantDashboardController;
use App\Http\Controllers\Web\AspirantToolController;
use App\Http\Controllers\Admin\CandidateTokenPackageController;
use App\Http\Controllers\Admin\CandidateTokenRateController;
use App\Http\Controllers\Admin\CandidateTokenPurchaseController;
use App\Http\Controllers\Admin\CandidateTokenLedgerController;
use App\Http\Controllers\Admin\SmsBalanceRequestController;
use App\Http\Controllers\Web\AspirantTokenController;
use App\Http\Controllers\Web\AspirantSmsBalanceRequestController;
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
    Route::get('/aspirants/claim/{candidate}/{token}', [CandidateClaimController::class, 'show'])->name('aspirants.claim.show');
    Route::post('/aspirants/claim/{candidate}/{token}', [CandidateClaimController::class, 'store'])->middleware('throttle:6,1')->name('aspirants.claim.store');
    Route::get('/aspirants', [CandidateController::class, 'publicIndex'])->name('aspirants.public');
    Route::get('/aspirants/{candidate}', [CandidateController::class, 'publicShow'])->name('aspirants.show');
});


// ====================== AUTHENTICATED ROUTES ======================
Route::middleware('auth')->group(function () {
    Route::middleware('aspirant')->group(function () {
        Route::get('/aspirant/dashboard', AspirantDashboardController::class)->name('aspirant.dashboard');
        Route::get('/aspirant/tools/{key}', [AspirantToolController::class, 'show'])->name('aspirant.tools.show');
        Route::get('/aspirant/tokens', [AspirantTokenController::class, 'index'])->name('aspirant.tokens.index');
        Route::post('/aspirant/tokens/purchase', [AspirantTokenController::class, 'purchase'])->name('aspirant.tokens.purchase');
        Route::post('/aspirant/sms-balance-requests', [AspirantSmsBalanceRequestController::class, 'store'])->middleware('throttle:6,10')->name('aspirant.sms-balance-requests.store');
        Route::get('/aspirant/campaign-website/samples', [AspirantToolController::class, 'websiteSamples'])->name('aspirant.campaign-website.samples');
        Route::post('/aspirant/tools/bulk-sms/send', [AspirantToolController::class, 'sendBulkSms'])->name('aspirant.tools.bulk-sms.send');
        Route::post('/aspirant/tools/opinion-polls/polls', [AspirantToolController::class, 'storePoll'])->name('aspirant.tools.polls.store');
        Route::post('/aspirant/tools/call-center/script', [AspirantToolController::class, 'saveCallScript'])->middleware('throttle:10,10')->name('aspirant.tools.call-center.script');
        Route::post('/aspirant/tools/call-center/calls', [AspirantToolController::class, 'storeCallLog'])->middleware('throttle:60,1')->name('aspirant.tools.call-center.calls');
        Route::post('/aspirant/tools/campaign-website/request', [AspirantToolController::class, 'storeWebsiteRequest'])->middleware('throttle:3,10')->name('aspirant.tools.campaign-website.request');
    });

    Route::middleware('admin')->group(function () {
        // --- Core Admin & Dashboard ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->middleware('permission:voters.view')->name('dashboard.stats');
        Route::get('/dashboard/stations', [DashboardController::class, 'stations'])->middleware('permission:data.view')->name('dashboard.stations');
        Route::post('/stations', [DashboardController::class, 'storeStation'])->middleware('permission:data.create')->name('stations.store');
        Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->middleware('permission:messages.view')->name('dashboard.messages');
        Route::get('/dashboard/donors', [DashboardController::class, 'donors'])->middleware('permission:finance.view')->name('dashboard.donors');
        Route::get('/live-stat-figures', [LiveStatFigureController::class, 'index'])->middleware('permission:live-stats.view')->name('live-stat-figures.index');
        Route::post('/live-stat-figures', [LiveStatFigureController::class, 'store'])->middleware('permission:live-stats.create')->name('live-stat-figures.store');
        Route::delete('/live-stat-figures/batches/{batchId}', [LiveStatFigureController::class, 'destroyBatch'])->middleware('permission:live-stats.delete')->name('live-stat-figures.batches.destroy');
        Route::delete('/live-stat-figures/{liveStatFigure}', [LiveStatFigureController::class, 'destroy'])->middleware('permission:live-stats.delete')->name('live-stat-figures.destroy');
        
        Route::get('/smtp', [SmtpController::class, 'index'])->middleware('permission:settings.view')->name('admin.smtp');
        Route::post('/smtp', [SmtpController::class, 'update'])->middleware('permission:settings.update')->name('admin.smtp.update');

        // --- Content Management ---
        Route::resource('positions', PositionController::class)->except(['show'])->middleware('permission:aspirants.view');
        Route::get('/candidates/search', [CandidateController::class, 'search'])->middleware('permission:aspirants.view')->name('candidates.search');
        Route::patch('/candidates/{candidate}/featured', [CandidateController::class, 'toggleFeatured'])->middleware('permission:aspirants.update')->name('candidates.featured');
        Route::patch('/candidates/{candidate}/approval', [CandidateController::class, 'updateApproval'])->middleware('permission:aspirants.approve')->name('candidates.approval');
        Route::post('/candidates/{candidate}/claim-link', [CandidateController::class, 'sendClaimLink'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('candidates.claim-link');
        Route::resource('candidates', CandidateController::class)->middleware('permission:aspirants.view');
        Route::resource('tags', TagController::class)->only(['index', 'store', 'destroy'])->middleware('permission:frontend.view');
        Route::resource('/admin/political-parties', PoliticalPartyController::class)
            ->parameters(['political-parties' => 'politicalParty'])
            ->names('political-parties')
            ->except(['show'])->middleware('permission:parties.view');
        Route::resource('/admin/coalitions', CoalitionController::class)
            ->parameters(['coalitions' => 'coalition'])
            ->names('coalitions')
            ->except(['show'])->middleware('permission:parties.view');
        
        Route::get('/news', [NewsArticleController::class, 'index'])->middleware('permission:frontend.view')->name('news.index');
        Route::get('/news.create', [NewsArticleController::class, 'create'])->middleware('permission:frontend.view')->name('news.create');
        Route::post('/news', [NewsArticleController::class, 'store'])->middleware('permission:frontend.update')->name('news.store');
        Route::get('/news/{news}/edit', [NewsArticleController::class, 'edit'])->middleware('permission:frontend.view')->name('news.edit');
        Route::put('/news/{news}', [NewsArticleController::class, 'update'])->middleware('permission:frontend.update')->name('news.update');
        Route::delete('/news/{news}', [NewsArticleController::class, 'destroy'])->middleware('permission:frontend.update')->name('news.destroy');

        Route::resource('candidate-token-packages', CandidateTokenPackageController::class)->except(['show'])->middleware('permission:tokens.view');
        Route::resource('candidate-token-rates', CandidateTokenRateController::class)->except(['show'])->middleware('permission:tokens.view');
        Route::get('/candidate-token-purchases', [CandidateTokenPurchaseController::class, 'index'])->middleware('permission:tokens.view')->name('candidate-token-purchases.index');
        Route::get('/candidate-token-ledger', [CandidateTokenLedgerController::class, 'index'])->middleware('permission:tokens.view')->name('candidate-token-ledger.index');
        Route::get('/sms-balance-requests', [SmsBalanceRequestController::class, 'index'])->middleware('permission:messages.view')->name('sms-balance-requests.index');
        Route::patch('/sms-balance-requests/{candidateSmsBalanceRequest}', [SmsBalanceRequestController::class, 'update'])->middleware('permission:messages.create')->name('sms-balance-requests.update');

        Route::resource('/admin/campaign-tools', CampaignToolController::class)
            ->parameters(['campaign-tools' => 'campaignTool'])
            ->names('campaign-tools')
            ->except(['show'])->middleware('permission:aspirants.view');
        Route::get('/admin/campaign-website-requests', [CampaignWebsiteRequestController::class, 'index'])->middleware('permission:aspirants.view')->name('campaign-website-requests.index');
        Route::patch('/admin/campaign-website-requests/{campaignWebsiteRequest}', [CampaignWebsiteRequestController::class, 'update'])->middleware('throttle:30,1')->name('campaign-website-requests.update');
        Route::get('/admin/campaign-website-samples', [CampaignWebsiteSampleController::class, 'index'])->middleware('permission:aspirants.view')->name('campaign-website-samples.index');
        Route::post('/admin/campaign-website-samples', [CampaignWebsiteSampleController::class, 'store'])->middleware('throttle:10,10')->name('campaign-website-samples.store');
        Route::delete('/admin/campaign-website-samples/{campaignWebsiteSample}', [CampaignWebsiteSampleController::class, 'destroy'])->middleware('throttle:30,1')->name('campaign-website-samples.destroy');
        Route::get('/admin/frontend-pages', [AdminFrontendPageController::class, 'index'])->middleware('permission:frontend.view')->name('frontend-pages.index');
        Route::get('/admin/frontend-pages/{page}/edit', [AdminFrontendPageController::class, 'edit'])->middleware('permission:frontend.view')->name('frontend-pages.edit');
        Route::put('/admin/frontend-pages/{page}', [AdminFrontendPageController::class, 'update'])->middleware('permission:frontend.update')->name('frontend-pages.update');

        // --- Finance & Donors ---
        Route::resource('payment-methods', PaymentMethodController::class)->names('payment-methods')->middleware('permission:finance.view');
        Route::resource('donors', DonorController::class)->names('donors')->middleware('permission:finance.view');

        // --- Geography (Core Data) ---
        Route::resource('/blocs', BlocController::class)->names('blocs')->middleware('permission:data.view');
        Route::resource('/counties', CountyController::class)->names('counties')->middleware('permission:data.view');
        Route::resource('/constituencies', ConstituencyController::class)->names('constituencies')->middleware('permission:data.view');
        Route::resource('/wards', WardController::class)->names('wards')->middleware('permission:data.view');
        Route::get('/locations', [LocationController::class, 'adminIndex'])->middleware('permission:voters.view')->name('locations.index');

        // Geography Imports
        Route::post('/blocs/import', [BlocController::class, 'import'])->middleware('permission:data.import')->name('blocs.import');
        Route::post('/counties/import', [CountyController::class, 'import'])->middleware('permission:data.import')->name('counties.import');
        Route::post('/constituencies/import', [ConstituencyController::class, 'import'])->middleware('permission:data.import')->name('constituencies.import');
        Route::post('/wards/import', [WardController::class, 'import'])->middleware('permission:data.import')->name('wards.import');
        Route::post('/stations/import', [DashboardController::class, 'importStations'])->middleware('permission:data.import')->name('stations.import');

        // --- User Access, Voter & Group Management ---
        Route::get('/user-access', [UserAccessController::class, 'index'])->name('user-access.index');
        Route::post('/user-access/admins', [UserAccessController::class, 'store'])->name('user-access.admins.store');
        Route::patch('/user-access/users/{user}/role', [UserAccessController::class, 'updateRole'])->name('user-access.roles.update');
        Route::patch('/user-access/roles/{role}/permissions', [UserAccessController::class, 'updatePermissions'])->name('user-access.permissions.update');
        Route::resource('users', UserController::class)->except(['show'])->middleware('permission:voters.view');
        Route::resource('groups', GroupController::class)->only(['create', 'store', 'show'])->middleware('permission:messages.create');
        Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])->middleware('permission:messages.create')->name('groups.messages.store');

        // --- Messages Management ---
        Route::get('/messages/create', [MessageController::class, 'createMessageForm'])->middleware('permission:messages.create')->name('messages.create');
        Route::post('/messages', [MessageController::class, 'storeMessageFromWeb'])->middleware('permission:messages.create')->name('messages.store');
        Route::get('/messages', [MessageController::class, 'index'])->middleware('permission:messages.view')->name('messages.index');
    });

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';
