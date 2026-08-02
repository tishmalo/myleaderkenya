<?php

namespace Tests\Unit\PublicPulse;

use App\Contracts\Repositories\Web\PublicPulseHomepageRepositoryInterface;
use App\Models\Candidate;
use App\Models\PublicPulseHomepageCandidate;
use App\Models\PublicPulseJob;
use App\Services\PublicPulse\PublicPulseHomepageService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class HomepageServiceTest extends TestCase
{
    public function test_cards_use_latest_completed_pulse_score_and_manual_order(): void
    {
        $repository = Mockery::mock(PublicPulseHomepageRepositoryInterface::class);
        $first = new Candidate(['name' => 'Candidate One', 'profile_picture' => 'https://example.test/one.jpg']);
        $first->id = 1;
        $second = new Candidate(['name' => 'Candidate Two', 'profile_picture' => 'https://example.test/two.jpg']);
        $second->id = 2;

        $selectionOne = new PublicPulseHomepageCandidate(['candidate_id' => 2, 'sort_order' => 1]);
        $selectionTwo = new PublicPulseHomepageCandidate(['candidate_id' => 1, 'sort_order' => 2]);
        $jobOne = new PublicPulseJob(['status' => 'completed', 'summary' => ['pulse_score' => -24.5, 'overall_confidence' => 'medium']]);
        $jobOne->candidate_id = 1;
        $jobTwo = new PublicPulseJob(['status' => 'completed', 'summary' => ['pulse_score' => 61.2, 'overall_confidence' => 'high']]);
        $jobTwo->candidate_id = 2;

        $repository->shouldReceive('selections')->once()->andReturn(collect([2 => $selectionOne, 1 => $selectionTwo]));
        $repository->shouldReceive('presidentialCandidates')->once()->andReturn(collect([1 => $first, 2 => $second]));
        $repository->shouldReceive('latestCompletedJobs')->once()->with([2, 1])->andReturn(collect([1 => $jobOne, 2 => $jobTwo]));

        $cards = (new PublicPulseHomepageService($repository))->cards();

        $this->assertSame(['Candidate Two', 'Candidate One'], array_column($cards, 'name'));
        $this->assertSame([61.2, -24.5], array_column($cards, 'approval'));
        $this->assertSame(['high', 'medium'], array_column($cards, 'confidence'));
    }

    public function test_candidate_without_completed_result_is_not_public(): void
    {
        $repository = Mockery::mock(PublicPulseHomepageRepositoryInterface::class);
        $candidate = new Candidate(['name' => 'Candidate One', 'profile_picture' => 'https://example.test/one.jpg']);
        $candidate->id = 1;
        $selection = new PublicPulseHomepageCandidate(['candidate_id' => 1, 'sort_order' => 1]);

        $repository->shouldReceive('selections')->once()->andReturn(collect([1 => $selection]));
        $repository->shouldReceive('presidentialCandidates')->once()->andReturn(collect([1 => $candidate]));
        $repository->shouldReceive('latestCompletedJobs')->once()->with([1])->andReturn(new Collection());

        $this->assertSame([], (new PublicPulseHomepageService($repository))->cards());
    }
}
