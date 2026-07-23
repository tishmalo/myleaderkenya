<?php

namespace App\Providers;

use App\Contracts\Repositories\Admin\BlocRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateSmsBalanceRequestRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenPackageRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenPurchaseRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenRateRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateTokenTransactionRepositoryInterface;
use App\Contracts\Repositories\Admin\CandidateSmsSettingRepositoryInterface;
use App\Contracts\Repositories\Admin\CampaignToolRepositoryInterface;
use App\Contracts\Repositories\Admin\CoalitionRepositoryInterface;
use App\Contracts\Repositories\Admin\PoliticalPartyRepositoryInterface;
use App\Contracts\Repositories\Admin\ConstituencyRepositoryInterface;
use App\Contracts\Repositories\Admin\CountyRepositoryInterface;
use App\Contracts\Repositories\Admin\DonorRepositoryInterface;
use App\Contracts\Repositories\Admin\LiveStatFigureRepositoryInterface;
use App\Contracts\Repositories\Admin\NewsArticleRepositoryInterface;
use App\Contracts\Repositories\Admin\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\Admin\PositionRepositoryInterface;
use App\Contracts\Repositories\Admin\SmtpRepositoryInterface;
use App\Contracts\Repositories\Admin\WardRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMemberRepositoryInterface;
use App\Contracts\Repositories\Api\GroupMessageRepositoryInterface;
use App\Contracts\Repositories\Api\GroupRepositoryInterface;
use App\Contracts\Repositories\Api\LocationRepositoryInterface;
use App\Contracts\Repositories\Api\MessageReactionRepositoryInterface;
use App\Contracts\Repositories\Api\MessageRepositoryInterface;
use App\Contracts\Repositories\Api\PollingStationRepositoryInterface;
use App\Contracts\Repositories\Api\StatsRepositoryInterface;
use App\Contracts\Repositories\Api\TagRepositoryInterface;
use App\Contracts\Repositories\Api\UserRepositoryInterface;
use App\Contracts\Repositories\Admin\SettingRepositoryInterface;
use App\Contracts\Repositories\Kenya\CountyRepositoryInterface as KenyaCountyRepositoryInterface;
use App\Repositories\Admin\BlocRepository;
use App\Repositories\Admin\CandidateRepository;
use App\Repositories\Admin\CandidateSmsBalanceRequestRepository;
use App\Repositories\Admin\CandidateTokenPackageRepository;
use App\Repositories\Admin\CandidateTokenPurchaseRepository;
use App\Repositories\Admin\CandidateTokenRateRepository;
use App\Repositories\Admin\CandidateTokenTransactionRepository;
use App\Repositories\Admin\CandidateSmsSettingRepository;
use App\Repositories\Admin\CampaignToolRepository;
use App\Repositories\Admin\CoalitionRepository;
use App\Repositories\Admin\PoliticalPartyRepository;
use App\Repositories\Admin\ConstituencyRepository;
use App\Repositories\Admin\CountyRepository;
use App\Repositories\Admin\DonorRepository;
use App\Repositories\Admin\LiveStatFigureRepository;
use App\Repositories\Admin\NewsArticleRepository;
use App\Repositories\Admin\PaymentMethodRepository;
use App\Repositories\Admin\PositionRepository;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\SmtpRepository;
use App\Repositories\Admin\WardRepository;
use App\Repositories\Api\GroupMemberRepository;
use App\Repositories\Api\GroupMessageRepository;
use App\Repositories\Api\GroupRepository;
use App\Repositories\Api\LocationRepository;
use App\Repositories\Api\MessageReactionRepository;
use App\Repositories\Api\MessageRepository;
use App\Repositories\Api\PollingStationRepository;
use App\Repositories\Api\StatsRepository;
use App\Repositories\Api\TagRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\Kenya\KenyaDataRepository;
use App\Contracts\Repositories\Web\LandingRepositoryInterface;
use App\Contracts\Repositories\Web\PublicApprovalRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateSmsMessageRepositoryInterface;
use App\Contracts\Repositories\Web\CandidateTokenWalletRepositoryInterface;
use App\Repositories\Web\LandingRepository;
use App\Repositories\Web\PublicApprovalRepository;
use App\Repositories\Web\CandidateSmsMessageRepository;
use App\Repositories\Web\CandidateTokenWalletRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Role;
use App\Observers\CandidateObserver;
use App\Policies\UserAccessPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Admin Repositories
        $this->app->bind(BlocRepositoryInterface::class, BlocRepository::class);
        $this->app->bind(CandidateRepositoryInterface::class, CandidateRepository::class);
        $this->app->bind(CandidateSmsBalanceRequestRepositoryInterface::class, CandidateSmsBalanceRequestRepository::class);
        $this->app->bind(CandidateTokenPackageRepositoryInterface::class, CandidateTokenPackageRepository::class);
        $this->app->bind(CandidateTokenPurchaseRepositoryInterface::class, CandidateTokenPurchaseRepository::class);
        $this->app->bind(CandidateTokenRateRepositoryInterface::class, CandidateTokenRateRepository::class);
        $this->app->bind(CandidateTokenTransactionRepositoryInterface::class, CandidateTokenTransactionRepository::class);
        $this->app->bind(CandidateSmsSettingRepositoryInterface::class, CandidateSmsSettingRepository::class);
        $this->app->bind(CampaignToolRepositoryInterface::class, CampaignToolRepository::class);
        $this->app->bind(CoalitionRepositoryInterface::class, CoalitionRepository::class);
        $this->app->bind(PoliticalPartyRepositoryInterface::class, PoliticalPartyRepository::class);
        $this->app->bind(DonorRepositoryInterface::class, DonorRepository::class);
        $this->app->bind(LiveStatFigureRepositoryInterface::class, LiveStatFigureRepository::class);
        $this->app->bind(NewsArticleRepositoryInterface::class, NewsArticleRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(SmtpRepositoryInterface::class, SmtpRepository::class);
        $this->app->bind(WardRepositoryInterface::class, WardRepository::class);
        $this->app->bind(CountyRepositoryInterface::class, CountyRepository::class);
        $this->app->bind(ConstituencyRepositoryInterface::class, ConstituencyRepository::class);
        
        $this->app->bind(\App\Contracts\Repositories\Admin\UserRepositoryInterface::class, \App\Repositories\Admin\UserRepository::class);
        $this->app->bind(\App\Contracts\Repositories\Admin\DashboardRepositoryInterface::class, \App\Repositories\Admin\DashboardRepository::class);
        $this->app->bind(\App\Contracts\Repositories\Admin\GroupRepositoryInterface::class, \App\Repositories\Admin\GroupRepository::class);
        $this->app->bind(\App\Contracts\Repositories\Admin\LocationRepositoryInterface::class, \App\Repositories\Admin\LocationRepository::class);
        $this->app->bind(\App\Contracts\Repositories\Admin\TagRepositoryInterface::class, \App\Repositories\Admin\TagRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(\App\Contracts\Repositories\Admin\PaymentMethodRepositoryInterface::class, \App\Repositories\Admin\PaymentMethodRepository::class);

        // Register Api Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(PollingStationRepositoryInterface::class, PollingStationRepository::class);
        $this->app->bind(StatsRepositoryInterface::class, StatsRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(GroupMemberRepositoryInterface::class, GroupMemberRepository::class);
        $this->app->bind(GroupMessageRepositoryInterface::class, GroupMessageRepository::class);
        $this->app->bind(MessageReactionRepositoryInterface::class, MessageReactionRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);

        //Register Kenya Data Repository
        $this->app->bind(KenyaCountyRepositoryInterface::class, KenyaDataRepository::class);

        // Register Web Repositories
        $this->app->bind(LandingRepositoryInterface::class, LandingRepository::class);
        $this->app->bind(PublicApprovalRepositoryInterface::class, PublicApprovalRepository::class);
        $this->app->bind(CandidateSmsMessageRepositoryInterface::class, CandidateSmsMessageRepository::class);
        $this->app->bind(CandidateTokenWalletRepositoryInterface::class, CandidateTokenWalletRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, UserAccessPolicy::class);
        Candidate::observe(CandidateObserver::class);

        // Configure rate limiters for API routes
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Strict rate limit for authentication endpoints (prevent brute force)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // General API rate limiting (per user or IP)
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Web rate limiting for public routes
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Stricter limit for data-intensive operations
        RateLimiter::for('api-heavy', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });
    }
}





