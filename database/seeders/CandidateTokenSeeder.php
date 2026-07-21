<?php

namespace Database\Seeders;

use App\Models\CandidateTokenPackage;
use App\Models\CandidateTokenRate;
use Illuminate\Database\Seeder;

class CandidateTokenSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Starter', 'token_amount' => 100, 'price' => 1000, 'currency' => 'KES', 'description' => 'Entry package for light campaign activity.', 'sort_order' => 10],
            ['name' => 'Growth', 'token_amount' => 300, 'price' => 2500, 'currency' => 'KES', 'description' => 'Useful for weekly voter engagement.', 'sort_order' => 20],
            ['name' => 'Campaign', 'token_amount' => 800, 'price' => 6000, 'currency' => 'KES', 'description' => 'Larger package for active outreach teams.', 'sort_order' => 30],
        ])->each(fn (array $package): CandidateTokenPackage => CandidateTokenPackage::updateOrCreate(['name' => $package['name']], $package + ['is_active' => true]));

        collect([
            ['action_key' => 'bulk-sms', 'label' => 'Bulk SMS', 'calculation_type' => 'per_sms_unit', 'token_amount' => 1, 'description' => 'Charged per valid recipient per SMS segment.', 'sort_order' => 10],
            ['action_key' => 'poll-draft', 'label' => 'Poll Draft Save', 'calculation_type' => 'fixed', 'token_amount' => 1, 'description' => 'Charged when saving an opinion poll draft.', 'sort_order' => 20],
            ['action_key' => 'poll-publish', 'label' => 'Poll Publish', 'calculation_type' => 'per_recipient', 'token_amount' => 1, 'description' => 'Charged per voter in the scoped poll audience.', 'sort_order' => 30],
            ['action_key' => 'call-script-save', 'label' => 'Call Script Save', 'calculation_type' => 'fixed', 'token_amount' => 1, 'description' => 'Charged when saving a call center script.', 'sort_order' => 40],
            ['action_key' => 'call-log', 'label' => 'Call Log', 'calculation_type' => 'fixed', 'token_amount' => 1, 'description' => 'Charged for each recorded call outcome.', 'sort_order' => 50],
            ['action_key' => 'campaign-website-request', 'label' => 'Campaign Website Request', 'calculation_type' => 'fixed', 'token_amount' => 10, 'description' => 'Charged when submitting a campaign website request.', 'sort_order' => 60],
        ])->each(fn (array $rate): CandidateTokenRate => CandidateTokenRate::updateOrCreate(['action_key' => $rate['action_key']], $rate + ['is_active' => true]));
    }
}
