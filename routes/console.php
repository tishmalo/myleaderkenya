<?php

use App\Jobs\CheckPublicPulseSourceAccountHealth;
use App\Jobs\RefreshPublicApprovalScores;
use App\Jobs\SyncPublicPulseJobs;
use App\Jobs\VerifyMissingParliamentMemberDetails;
use App\Console\Commands\PurgeCampaignSpam;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(PurgeCampaignSpam::class)
    ->dailyAt('03:30')
    ->timezone('Africa/Nairobi')
    ->withoutOverlapping();

Schedule::job(new RefreshPublicApprovalScores)
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::job(new VerifyMissingParliamentMemberDetails)
    ->sundays()
    ->at('23:00')
    ->timezone('Africa/Nairobi')
    ->withoutOverlapping();

Schedule::job(new CheckPublicPulseSourceAccountHealth)
    ->everyThirtyMinutes()
    ->withoutOverlapping();

Schedule::job(new SyncPublicPulseJobs)
    ->everyFiveMinutes()
    ->withoutOverlapping();

