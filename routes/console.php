<?php

use App\Jobs\RefreshPublicApprovalScores;
use App\Jobs\VerifyMissingParliamentMemberDetails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RefreshPublicApprovalScores)
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::job(new VerifyMissingParliamentMemberDetails)
    ->sundays()
    ->at('23:00')
    ->timezone('Africa/Nairobi')
    ->withoutOverlapping();
