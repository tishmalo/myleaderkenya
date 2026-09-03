<?php

use App\Http\Controllers\Admin\AspirantImpersonationController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\BlocController;
use App\Http\Controllers\Admin\CampaignPriorityCategoryController;
use App\Http\Controllers\Admin\CampaignToolController;
use App\Http\Controllers\Admin\CampaignToolPackageController;
use App\Http\Controllers\Admin\CampaignToolRequestController;
use App\Http\Controllers\Admin\CampaignWebsiteRequestController;
use App\Http\Controllers\Admin\CampaignWebsiteSampleController;
use App\Http\Controllers\Admin\CandidateCampaignPriorityReviewController;
use App\Http\Controllers\Admin\CandidateClaimRequestController as AdminCandidateClaimRequestController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CandidateTokenLedgerController;
use App\Http\Controllers\Admin\CandidateTokenPackageController;
use App\Http\Controllers\Admin\CandidateTokenPurchaseController;
use App\Http\Controllers\Admin\CandidateTokenRateController;
use App\Http\Controllers\Admin\CoalitionController;
use App\Http\Controllers\Admin\ConstituencyController;
use App\Http\Controllers\Admin\CountyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FrontendPageController as AdminFrontendPageController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\KittyTypeController;
use App\Http\Controllers\Admin\LiveStatFigureController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NewsArticleController;
use App\Http\Controllers\Admin\NotificationEmailController;
use App\Http\Controllers\Admin\ParliamentMemberController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PoliticalPartyController;
use App\Http\Controllers\Admin\PoliticalPartyManagementController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PublicPulseController;
use App\Http\Controllers\Admin\PublicPulseJobController;
use App\Http\Controllers\Admin\PublicPulseSourceAccountController;
use App\Http\Controllers\Admin\RecaptchaSettingController;
use App\Http\Controllers\Admin\SmsBalanceRequestController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Admin\SupportGroupTypeController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Web\AspirantAuditController;
use App\Http\Controllers\Web\AspirantDashboardController;
use App\Http\Controllers\Web\AspirantEventController;
use App\Http\Controllers\Web\AspirantNewsArticleController;
use App\Http\Controllers\Web\AspirantRegistrationController;
use App\Http\Controllers\Web\AspirantSmsBalanceRequestController;
use App\Http\Controllers\Web\AspirantSupportController;
use App\Http\Controllers\Web\AspirantTokenController;
use App\Http\Controllers\Web\AspirantToolActivationRequestController;
use App\Http\Controllers\Web\AspirantToolController;
use App\Http\Controllers\Web\CampaignToolPaymentController;
use App\Http\Controllers\Web\CandidateCampaignPriorityController;
use App\Http\Controllers\Web\CandidateClaimController;
use App\Http\Controllers\Web\CandidateClaimRequestController;
use App\Http\Controllers\Web\DonorToolboxController;
use App\Http\Controllers\Web\EventController as WebEventController;
use App\Http\Controllers\Web\FrontendPageController as PublicFrontendPageController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\MyAccountController;
use App\Http\Controllers\Web\PoliticalPartyAccountRequestController;
use App\Http\Controllers\Web\PoliticalPartyDashboardController;
use App\Http\Controllers\Web\UserEventController;
use App\Http\Controllers\Web\UserNewsArticleController;
use App\Http\Controllers\Web\UserProfileController;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Candidate location JSON helpers used by the admin candidate form.
Route::get('/api/counties', function () {
    return County::query()
        ->orderBy('name')
        ->get(['id', 'name']);
})->middleware('throttle:api');

Route::get('/api/constituencies', function (Request $request) {
    return Constituency::query()
        ->when($request->query('county_id'), fn ($query, $countyId) => $query->where('county_id', $countyId))
        ->orderBy('name')
        ->get(['id', 'name', 'county_id']);
})->middleware('throttle:api');

Route::get('/api/wards', function (Request $request) {
    return Ward::query()
        ->when($request->query('constituency_id'), fn ($query, $constituencyId) => $query->where('constituency_id', $constituencyId))
        ->get(['id', 'name', 'constituency_id'])
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();
})->middleware('throttle:api');

// ====================== PUBLIC ROUTES (Throttled) ======================
Route::middleware('throttle:web')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
    Route::get('/featured-aspirants', [LandingController::class, 'featuredAspirants'])->middleware('throttle:public-data')->name('landing.featured-aspirants');
    Route::get('/about-us', [PublicFrontendPageController::class, 'about'])->name('about.public');
    Route::get('/live-stats', [PublicFrontendPageController::class, 'liveStats'])->name('live-stats.public');
    Route::get('/download-app', [PublicFrontendPageController::class, 'downloadApp'])->name('download-app.public');
    Route::get('/contact-us', [PublicFrontendPageController::class, 'contact'])->name('contact.public');

    Route::get('/privacy', function () {
        return view('privacy');
    })->name('privacy');

    // Public Campaign Tools, News & Aspirants
    Route::get('/campaign-tools', [CampaignToolController::class, 'publicIndex'])->name('campaign-tools.public');
    Route::post('/campaign-tools/{campaignTool}/requests', [CampaignToolController::class, 'storeFeatureRequest'])->middleware('throttle:campaignToolRequests')->name('campaign-tools.requests.store');
    Route::get('/campaign-tools/{slug}', [CampaignToolController::class, 'publicShow'])->name('campaign-tools.show');

    Route::get('/parties', [PoliticalPartyController::class, 'publicIndex'])->name('parties.public');
    Route::get('/parties/{slug}', [PoliticalPartyController::class, 'publicShow'])->middleware('throttle:public-data')->name('parties.show');
    Route::get('/parties/{politicalParty}/request-access', [PoliticalPartyAccountRequestController::class, 'create'])->name('parties.access.create');
    Route::post('/parties/{politicalParty}/request-access', [PoliticalPartyAccountRequestController::class, 'store'])->middleware('throttle:3,10')->name('parties.access.store');
    Route::get('/coalitions', [CoalitionController::class, 'publicIndex'])->name('coalitions.public');
    Route::get('/coalitions/{slug}', [CoalitionController::class, 'publicShow'])->name('coalitions.show');

    Route::get('/news/public', [NewsArticleController::class, 'publicIndex'])->name('news.public');
    Route::get('/news/{slug}', [NewsArticleController::class, 'publicShow'])->name('news.public.show');

    // Legacy alias so the old /events/create admin URL keeps working.
    Route::get('/events/create', fn () => redirect()->route('events.create'));

    Route::get('/events', [WebEventController::class, 'index'])->name('events.public');
    Route::get('/events/{slug}', [WebEventController::class, 'show'])->name('events.show');
    Route::post('/events/{slug}/register', [WebEventController::class, 'register'])->middleware('throttle:6,10')->name('events.register');
    Route::get('/events/tickets/{code}', [WebEventController::class, 'ticket'])->name('events.ticket');

    Route::get('/aspirants/search', [AspirantRegistrationController::class, 'search'])
        ->middleware(['throttle:30,1', 'cache.headers:no_store;private'])
        ->name('aspirants.search');
    Route::post('/aspirants/email-availability', [AspirantRegistrationController::class, 'emailAvailability'])
        ->middleware(['throttle:20,1', 'cache.headers:no_store;private'])
        ->name('aspirants.email-availability');
    Route::get('/aspirants/register', [AspirantRegistrationController::class, 'create'])
        ->middleware('cache.headers:no_store;private')
        ->name('aspirants.register');
    Route::post('/aspirants/register', [AspirantRegistrationController::class, 'store'])->middleware(['throttle:3,10', 'cache.headers:no_store;private'])->name('aspirants.register.store');
    Route::get('/aspirants/claim/{candidate}/{token}', [CandidateClaimController::class, 'show'])->middleware('cache.headers:no_store;private')->name('aspirants.claim.show');
    Route::post('/aspirants/claim/{candidate}/{token}', [CandidateClaimController::class, 'store'])->middleware(['throttle:6,1', 'cache.headers:no_store;private'])->name('aspirants.claim.store');
    Route::post('/aspirants/{candidate}/claim-requests', [CandidateClaimRequestController::class, 'store'])->middleware(['throttle:3,10', 'cache.headers:no_store;private'])->name('aspirants.claim-requests.store');
    Route::get('/aspirants', [CandidateController::class, 'publicIndex'])->middleware('throttle:public-data')->name('aspirants.public');
    Route::get('/aspirants/{candidate}', [CandidateController::class, 'publicShow'])->middleware('throttle:public-data')->name('aspirants.show');
});

Route::get('/payments/ipay/callback', [AspirantTokenController::class, 'ipayCallback'])->name('payments.ipay.callback');
Route::get('/party/payments/ipay/callback', [PoliticalPartyDashboardController::class, 'callback'])->name('party.payments.ipay.callback');
Route::get('/toolbox/payments/ipay/callback', [DonorToolboxController::class, 'callback'])->name('toolbox.payments.ipay.callback');
Route::get('/toolbox/supports/ipay/callback', [DonorToolboxController::class, 'supportCallback'])->name('toolbox.supports.ipay.callback');
Route::get('/events/payment/callback', [WebEventController::class, 'callback'])->name('events.payment.callback');

// ====================== AUTHENTICATED ROUTES ======================
Route::middleware('auth')->group(function () {
    Route::get('/my-account/profile', [UserProfileController::class, 'edit'])->name('account.profile.edit');
    Route::put('/my-account/profile', [UserProfileController::class, 'update'])->middleware('throttle:10,1')->name('account.profile.update');

    Route::middleware('profile.complete')->group(function () {
        Route::get('/my-account', [MyAccountController::class, 'index'])->name('my-account');
        Route::post('/my-account/aspirants/select', [MyAccountController::class, 'select'])->middleware('throttle:20,1')->name('my-account.aspirants.select');
        Route::get('/my-account/toolbox', [DonorToolboxController::class, 'index'])->name('account.toolbox.index');
        Route::post('/my-account/toolbox/purchase', [DonorToolboxController::class, 'purchase'])->middleware('throttle:6,10')->name('account.toolbox.purchase');
        Route::post('/my-account/toolbox/adoptions/{campaignToolRequest}/pay', [DonorToolboxController::class, 'pay'])->middleware('throttle:10,1')->name('account.toolbox.adoptions.pay');
        Route::post('/my-account/toolbox/tools/{campaignToolRequest}/redeem', [CampaignToolPaymentController::class, 'redeem'])->middleware('throttle:10,1')->name('account.toolbox.tools.redeem');
        Route::get('/my-account/news', [UserNewsArticleController::class, 'index'])->name('account.news.index');
        Route::get('/my-account/news/submit', [UserNewsArticleController::class, 'create'])->name('account.news.create');
        Route::get('/my-account/news/candidates/search', [UserNewsArticleController::class, 'searchCandidates'])->middleware('throttle:30,1')->name('account.news.candidates.search');
        Route::post('/my-account/news', [UserNewsArticleController::class, 'store'])->middleware('throttle:3,10')->name('account.news.store');
        Route::get('/my-account/events', [UserEventController::class, 'index'])->name('account.events.index');
        Route::get('/my-account/events/submit', [UserEventController::class, 'create'])->name('account.events.create');
        Route::post('/my-account/events', [UserEventController::class, 'store'])->middleware('throttle:3,10')->name('account.events.store');
        Route::middleware(['aspirant', 'aspirant.owner'])->group(function () {
            Route::get('/aspirant/audits', [AspirantAuditController::class, 'index'])->name('aspirant.audits.index');
            Route::get('/aspirant/audits/{audit}', [AspirantAuditController::class, 'show'])->name('aspirant.audits.show');
        });
        Route::middleware('aspirant')->group(function () {
            Route::get('/aspirant/dashboard', AspirantDashboardController::class)->name('aspirant.dashboard');
            Route::get('/aspirant/news', [AspirantNewsArticleController::class, 'index'])->name('aspirant.news.index');
            Route::get('/aspirant/news/submit', [AspirantNewsArticleController::class, 'create'])->name('aspirant.news.create');
            Route::get('/aspirant/news/candidates/search', [AspirantNewsArticleController::class, 'searchCandidates'])->middleware('throttle:30,1')->name('aspirant.news.candidates.search');
            Route::post('/aspirant/news', [AspirantNewsArticleController::class, 'store'])->middleware('throttle:3,10')->name('aspirant.news.store');
            Route::get('/aspirant/events', [AspirantEventController::class, 'index'])->name('aspirant.events.index');
            Route::get('/aspirant/events/create', [AspirantEventController::class, 'create'])->name('aspirant.events.create');
            Route::post('/aspirant/events', [AspirantEventController::class, 'store'])->middleware('throttle:3,10')->name('aspirant.events.store');
            Route::post('/aspirant/cover-photo', [AspirantDashboardController::class, 'updateCoverPhoto'])->middleware('throttle:6,10')->name('aspirant.cover-photo.update');
            Route::patch('/aspirant/social-links', [AspirantDashboardController::class, 'updateSocialLinks'])->middleware('throttle:20,1')->name('aspirant.social-links.update');
            Route::patch('/aspirant/media', [AspirantDashboardController::class, 'updateMedia'])->middleware('throttle:10,10')->name('aspirant.media.update');
            Route::put('/aspirant/campaign-priorities', [CandidateCampaignPriorityController::class, 'update'])->middleware('throttle:10,10')->name('aspirant.campaign-priorities.update');
            Route::delete('/aspirant/team/{member}', [AspirantDashboardController::class, 'removeTeamMember'])->middleware('throttle:20,1')->name('aspirant.team.destroy');
            Route::get('/aspirant/tools/{key}', [AspirantToolController::class, 'show'])->name('aspirant.tools.show');
            Route::post('/aspirant/tool-activation-requests', [AspirantToolActivationRequestController::class, 'store'])->middleware('throttle:6,10')->name('aspirant.tool-activation-requests.store');
            Route::get('/aspirant/tokens', [AspirantTokenController::class, 'index'])->name('aspirant.tokens.index');
            Route::get('/aspirant/support', [AspirantSupportController::class, 'index'])->name('aspirant.support.index');
            Route::post('/aspirant/support/{aspirantSupportPayment}/reply', [AspirantSupportController::class, 'reply'])->middleware('throttle:10,1')->name('aspirant.support.reply');
            Route::post('/aspirant/tokens/purchase', [AspirantTokenController::class, 'purchase'])->name('aspirant.tokens.purchase');
            Route::post('/aspirant/sms-balance-requests', [AspirantSmsBalanceRequestController::class, 'store'])->middleware('throttle:6,10')->name('aspirant.sms-balance-requests.store');
            Route::get('/aspirant/campaign-website/samples', [AspirantToolController::class, 'websiteSamples'])->name('aspirant.campaign-website.samples');
            Route::post('/aspirant/tools/bulk-sms/send', [AspirantToolController::class, 'sendBulkSms'])->name('aspirant.tools.bulk-sms.send');
            Route::post('/aspirant/tools/bulk-sms/contacts/import', [AspirantToolController::class, 'importBulkSmsContacts'])->middleware('throttle:6,10')->name('aspirant.tools.bulk-sms.contacts.import');
            Route::post('/aspirant/tools/opinion-polls/polls', [AspirantToolController::class, 'storePoll'])->name('aspirant.tools.polls.store');
            Route::post('/aspirant/tools/call-center/script', [AspirantToolController::class, 'saveCallScript'])->middleware('throttle:10,10')->name('aspirant.tools.call-center.script');
            Route::post('/aspirant/tools/call-center/calls', [AspirantToolController::class, 'storeCallLog'])->middleware('throttle:60,1')->name('aspirant.tools.call-center.calls');
            Route::post('/aspirant/tools/campaign-website/request', [AspirantToolController::class, 'storeWebsiteRequest'])->middleware('throttle:3,10')->name('aspirant.tools.campaign-website.request');
            Route::post('/aspirant/tools/support-groups/contacts', [AspirantToolController::class, 'storeSupportContact'])->middleware('throttle:30,1')->name('aspirant.tools.support-groups.contacts.store');
            Route::post('/aspirant/tools/support-groups/contacts/import', [AspirantToolController::class, 'importSupportContacts'])->middleware('throttle:6,10')->name('aspirant.tools.support-groups.contacts.import');
            Route::patch('/aspirant/tools/support-groups/contacts/{candidateSupportContact}', [AspirantToolController::class, 'updateSupportContact'])->middleware('throttle:30,1')->name('aspirant.tools.support-groups.contacts.update');
            Route::delete('/aspirant/tools/support-groups/contacts/{candidateSupportContact}', [AspirantToolController::class, 'destroySupportContact'])->middleware('throttle:30,1')->name('aspirant.tools.support-groups.contacts.destroy');
        });

        Route::middleware('party')->prefix('party')->name('party.')->group(function () {
            Route::get('/dashboard', [PoliticalPartyDashboardController::class, 'index'])->name('dashboard');
            Route::get('/aspirants/search/{context}', [PoliticalPartyDashboardController::class, 'searchCandidates'])->middleware('throttle:30,1')->name('candidates.search');
            Route::get('/aspirants/create', [PoliticalPartyDashboardController::class, 'createCandidate'])->name('candidates.create');
            Route::post('/aspirants', [PoliticalPartyDashboardController::class, 'storeCandidate'])->name('candidates.store');
            Route::get('/aspirants/{candidate}/edit', [PoliticalPartyDashboardController::class, 'editCandidate'])->name('candidates.edit');
            Route::put('/aspirants/{candidate}', [PoliticalPartyDashboardController::class, 'updateCandidate'])->name('candidates.update');
            Route::post('/aspirant-claims', [PoliticalPartyDashboardController::class, 'claim'])->name('claims.store');
            Route::post('/officials', [PoliticalPartyDashboardController::class, 'invite'])->name('officials.store');
            Route::delete('/officials/{user}', [PoliticalPartyDashboardController::class, 'removeOfficial'])->name('officials.destroy');
            Route::post('/tokens/purchase', [PoliticalPartyDashboardController::class, 'purchase'])->name('tokens.purchase');
            Route::post('/tokens/distribute', [PoliticalPartyDashboardController::class, 'distribute'])->name('tokens.distribute');
        });
        Route::middleware(['admin', 'superadmin'])->group(function () {
            Route::get('/admin/audits', [AuditController::class, 'index'])->name('audits.index');
            Route::get('/admin/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
        });
        Route::middleware('admin')->group(function () {
            // --- Aspirant CSV Import ---
            Route::get('candidates/import/template', [CandidateController::class, 'importTemplate'])
                ->middleware('permission:aspirants.create')
                ->name('candidates.import.template');
            Route::post('candidates/import', [CandidateController::class, 'import'])
                ->middleware('permission:aspirants.create')
                ->name('candidates.import');
            Route::post('candidates/{candidate}/import/publish', [CandidateController::class, 'publishImport'])
                ->middleware('permission:aspirants.approve')
                ->name('candidates.import.publish');
            Route::post('candidates/{candidate}/import/discard', [CandidateController::class, 'discardImport'])
                ->middleware('permission:aspirants.delete')
                ->name('candidates.import.discard');
            Route::post('candidates/export', [CandidateController::class, 'export'])
                ->middleware('permission:aspirants.create')
                ->name('candidates.export');
            Route::get('candidates/export/{run}/download', [CandidateController::class, 'exportDownload'])
                ->middleware('permission:aspirants.view')
                ->name('candidates.export.download');
            Route::get('candidates/transfer/{run}/status', [CandidateController::class, 'transferStatus'])
                ->middleware('permission:aspirants.view')
                ->name('candidates.transfer.status');

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
            Route::get('/recaptcha', [RecaptchaSettingController::class, 'index'])->middleware('permission:settings.view')->name('admin.recaptcha');
            Route::post('/recaptcha', [RecaptchaSettingController::class, 'update'])->middleware('permission:settings.update')->name('admin.recaptcha.update');
            Route::get('/notifications/emails', [NotificationEmailController::class, 'index'])->middleware('permission:settings.view')->name('notification-emails.index');
            Route::get('/notifications/emails/{key}/edit', [NotificationEmailController::class, 'edit'])->middleware('permission:settings.view')->name('notification-emails.edit');
            Route::put('/notifications/emails/{key}', [NotificationEmailController::class, 'update'])->middleware('permission:settings.update')->name('notification-emails.update');
            Route::post('/notifications/emails/{key}/toggle', [NotificationEmailController::class, 'toggle'])->middleware('permission:settings.update')->name('notification-emails.toggle');
            Route::post('/notifications/emails/{key}/test', [NotificationEmailController::class, 'sendTest'])->middleware('permission:settings.update')->name('notification-emails.test');

            // --- Content Management ---
            Route::resource('positions', PositionController::class)->except(['show'])->middleware('permission:aspirants.view');
            Route::get('/campaign-priority-categories', [CampaignPriorityCategoryController::class, 'index'])->middleware('permission:aspirants.view')->name('campaign-priority-categories.index');
            Route::post('/campaign-priority-categories', [CampaignPriorityCategoryController::class, 'store'])->middleware('permission:aspirants.create')->name('campaign-priority-categories.store');
            Route::put('/campaign-priority-categories/{campaignPriorityCategory}', [CampaignPriorityCategoryController::class, 'update'])->middleware('permission:aspirants.update')->name('campaign-priority-categories.update');
            Route::delete('/campaign-priority-categories/{campaignPriorityCategory}', [CampaignPriorityCategoryController::class, 'destroy'])->middleware('permission:aspirants.delete')->name('campaign-priority-categories.destroy');
            Route::patch('/candidates/{candidate}/campaign-priorities/{candidateCampaignPriority}', [CandidateCampaignPriorityReviewController::class, 'update'])->middleware(['permission:aspirants.approve', 'throttle:30,1'])->name('candidate-campaign-priorities.review');
            Route::get('/candidates/search', [CandidateController::class, 'search'])->middleware('permission:aspirants.view')->name('candidates.search');
            Route::patch('/candidates/{candidate}/featured', [CandidateController::class, 'toggleFeatured'])->middleware('permission:aspirants.update')->name('candidates.featured');
            Route::patch('/candidates/{candidate}/approval', [CandidateController::class, 'updateApproval'])->middleware('permission:aspirants.approve')->name('candidates.approval');
            Route::post('/candidates/{candidate}/claim-link', [CandidateController::class, 'sendClaimLink'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('candidates.claim-link');
            Route::post('/candidates/{candidate}/login-as/{user}', [AspirantImpersonationController::class, 'start'])->middleware(['permission:aspirants.update', 'throttle:20,1'])->name('candidates.login-as');
            Route::patch('/candidate-claim-requests/{claimRequest}', [AdminCandidateClaimRequestController::class, 'update'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('candidate-claim-requests.update');
            Route::patch('/candidate-claim-requests/{claimRequest}/dashboard-access', [AdminCandidateClaimRequestController::class, 'updateDashboardAccess'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('candidate-claim-requests.dashboard-access');
            Route::get('/admin/parliament-members', [ParliamentMemberController::class, 'index'])->middleware('permission:aspirants.view')->name('parliament-members.index');
            Route::post('/admin/parliament-members/import', [ParliamentMemberController::class, 'import'])->middleware(['permission:aspirants.approve', 'throttle:3,10'])->name('parliament-members.import');
            Route::patch('/admin/parliament-members/{parliamentMember}/link', [ParliamentMemberController::class, 'link'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('parliament-members.link');
            Route::patch('/admin/parliament-members/{parliamentMember}/publish', [ParliamentMemberController::class, 'publish'])->middleware(['permission:aspirants.approve', 'throttle:30,1'])->name('parliament-members.publish');
            Route::post('/admin/parliament-members/{parliamentMember}/retry', [ParliamentMemberController::class, 'retry'])->middleware(['permission:aspirants.update', 'throttle:30,1'])->name('parliament-members.retry');
            Route::resource('candidates', CandidateController::class)->middleware('permission:aspirants.view');
            Route::resource('tags', TagController::class)->only(['index', 'store', 'destroy'])->middleware('permission:frontend.view');
            Route::get('/admin/party-management', [PoliticalPartyManagementController::class, 'index'])->middleware('permission:parties.view')->name('party-management.index');
            Route::post('/admin/party-management/officials', [PoliticalPartyManagementController::class, 'storeOfficial'])->middleware('permission:parties.update')->name('party-management.officials.store');
            Route::patch('/admin/party-management/parties/{politicalParty}/officials/{user}', [PoliticalPartyManagementController::class, 'status'])->middleware('permission:parties.update')->name('party-management.officials.status');
            Route::patch('/admin/party-management/accounts/{accountRequest}', [PoliticalPartyManagementController::class, 'account'])->middleware('permission:parties.update')->name('party-management.accounts.update');
            Route::get('/admin/party-management/accounts/{accountRequest}/document', [PoliticalPartyManagementController::class, 'document'])->middleware('permission:parties.view')->name('party-management.accounts.document');
            Route::patch('/admin/party-management/claims/{claim}', [PoliticalPartyManagementController::class, 'claim'])->middleware('permission:parties.update')->name('party-management.claims.update');
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

            Route::resource('/admin/events', AdminEventController::class)->names('events')->except(['show'])->middleware('permission:frontend.view');
            Route::patch('/admin/events/{event}/approval', [AdminEventController::class, 'updateApproval'])->middleware(['permission:frontend.update', 'throttle:30,1'])->name('events.approval');
            Route::get('/admin/events/{event}/registrations', [AdminEventController::class, 'registrations'])->middleware('permission:frontend.view')->name('events.registrations');
            Route::post('/admin/events/{event}/registrations/{registration}/resend', [AdminEventController::class, 'resendTicketEmail'])->middleware('permission:frontend.view')->name('events.registrations.resend');
            Route::post('/admin/events/{event}/registrations/{registration}/tickets/generate', [AdminEventController::class, 'generateTickets'])->middleware('permission:frontend.view')->name('events.registrations.tickets.generate');
            Route::post('/admin/events/{event}/registrations/{registration}/tickets/{ticket}/check-in', [AdminEventController::class, 'checkInTicket'])->middleware('permission:frontend.view')->name('events.tickets.checkin');

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
            Route::post('/admin/campaign-tools/{campaignTool}/packages', [CampaignToolPackageController::class, 'store'])->middleware('permission:campaign-tool-requests.update')->name('campaign-tools.packages.store');
            Route::put('/admin/campaign-tools/{campaignTool}/packages/{package}', [CampaignToolPackageController::class, 'update'])->middleware('permission:campaign-tool-requests.update')->name('campaign-tools.packages.update');
            Route::delete('/admin/campaign-tools/{campaignTool}/packages/{package}', [CampaignToolPackageController::class, 'destroy'])->middleware('permission:campaign-tool-requests.update')->name('campaign-tools.packages.destroy');
            Route::get('/admin/campaign-tool-requests', [CampaignToolRequestController::class, 'index'])->middleware('permission:campaign-tool-requests.view')->name('campaign-tool-requests.index');
            Route::patch('/admin/campaign-tool-requests/{campaignToolRequest}', [CampaignToolRequestController::class, 'update'])->middleware('permission:campaign-tool-requests.update')->name('campaign-tool-requests.update');
            Route::delete('/admin/campaign-tool-requests/{campaignToolRequest}', [CampaignToolRequestController::class, 'destroy'])->middleware('permission:campaign-tool-requests.delete')->name('campaign-tool-requests.destroy');
            Route::get('/admin/public-pulse', [PublicPulseJobController::class, 'index'])->middleware('permission:frontend.view')->name('public-pulse.index');
            Route::post('/admin/public-pulse/jobs', [PublicPulseJobController::class, 'store'])->middleware(['permission:frontend.update', 'throttle:10,10'])->name('public-pulse.jobs.store');
            Route::put('/admin/public-pulse/homepage', [PublicPulseJobController::class, 'updateHomepage'])->middleware('permission:frontend.update')->name('public-pulse.homepage.update');
            Route::get('/admin/public-pulse/jobs/{publicPulseJob}', [PublicPulseJobController::class, 'show'])->middleware('permission:frontend.view')->name('public-pulse.jobs.show');
            Route::patch('/admin/public-pulse/jobs/{publicPulseJob}/sync', [PublicPulseJobController::class, 'sync'])->middleware(['permission:frontend.update', 'throttle:20,10'])->name('public-pulse.jobs.sync');
            Route::post('/admin/public-pulse/jobs/{publicPulseJob}/retry', [PublicPulseJobController::class, 'retry'])->middleware(['permission:frontend.update', 'throttle:10,10'])->name('public-pulse.jobs.retry');
            Route::get('/admin/public-pulse/legacy', [PublicPulseController::class, 'index'])->middleware('permission:frontend.view')->name('public-pulse.legacy');
            Route::get('/admin/public-pulse/x-sessions', [PublicPulseSourceAccountController::class, 'index'])->middleware('permission:frontend.view')->name('public-pulse.x-sessions.index');
            Route::post('/admin/public-pulse/x-sessions', [PublicPulseSourceAccountController::class, 'store'])->middleware(['permission:frontend.update', 'throttle:10,10'])->name('public-pulse.x-sessions.store');
            Route::put('/admin/public-pulse/x-sessions/{publicPulseSourceAccount}', [PublicPulseSourceAccountController::class, 'update'])->middleware(['permission:frontend.update', 'throttle:10,10'])->name('public-pulse.x-sessions.update');
            Route::patch('/admin/public-pulse/x-sessions/{publicPulseSourceAccount}/check', [PublicPulseSourceAccountController::class, 'check'])->middleware(['permission:frontend.update', 'throttle:20,10'])->name('public-pulse.x-sessions.check');
            Route::patch('/admin/public-pulse/x-sessions/{publicPulseSourceAccount}/replace', [PublicPulseSourceAccountController::class, 'replace'])->middleware('permission:frontend.update')->name('public-pulse.x-sessions.replace');
            Route::get('/admin/support-group-types', [SupportGroupTypeController::class, 'index'])->middleware('permission:support-groups.view')->name('support-group-types.index');
            Route::post('/admin/support-group-types', [SupportGroupTypeController::class, 'store'])->middleware('permission:support-groups.create')->name('support-group-types.store');
            Route::patch('/admin/support-group-types/{supportGroupType}', [SupportGroupTypeController::class, 'update'])->middleware('permission:support-groups.update')->name('support-group-types.update');
            Route::delete('/admin/support-group-types/{supportGroupType}', [SupportGroupTypeController::class, 'destroy'])->middleware('permission:support-groups.delete')->name('support-group-types.destroy');
            Route::get('/admin/kitty-types', [KittyTypeController::class, 'index'])->middleware('permission:tokens.view')->name('kitty-types.index');
            Route::post('/admin/kitty-types', [KittyTypeController::class, 'store'])->middleware('permission:tokens.create')->name('kitty-types.store');
            Route::patch('/admin/kitty-types/{kittyType}', [KittyTypeController::class, 'update'])->middleware('permission:tokens.update')->name('kitty-types.update');
            Route::delete('/admin/kitty-types/{kittyType}', [KittyTypeController::class, 'destroy'])->middleware('permission:tokens.delete')->name('kitty-types.destroy');
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
        Route::post('/impersonation/stop', [AspirantImpersonationController::class, 'stop'])->name('impersonation.stop')->withoutMiddleware('profile.complete');
    });
});
require __DIR__.'/auth.php';
