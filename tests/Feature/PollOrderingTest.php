<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The polls page reads like a ballot paper: presidential first and MCA last,
 * the leader of each poll on top, and voting returns you to the polls.
 */
class PollOrderingTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, Position> */
    private array $positions = [];

    private ?User $voterUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Ballot order as the positions table defines it.
        foreach (['President' => 10, 'Governor' => 20, 'Senator' => 30, 'Women Rep' => 40, 'MP' => 50, 'MCA' => 60] as $name => $order) {
            $this->positions[$name] = Position::create([
                'name' => $name,
                'description' => $name,
                'sort_order' => $order,
            ]);
        }
    }

    private function voter(): User
    {
        return $this->voterUser ??= User::factory()->create([
            'name' => 'Nairobi Voter',
            'username' => 'nairobi-voter',
            'role' => 'user',
            'role_id' => Role::idFor(Role::USER),
            'is_voter' => true,
            'county' => 'Nairobi',
            'constituency' => 'Dagoretti North',
            'ward' => 'Kileleshwa',
        ]);
    }

    private function candidate(string $name, string $positionName): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'position_id' => $this->positions[$positionName]->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'approval_status' => 'approved',
        ]);
    }

    private function poll(string $question, string $slug, array $candidateIds, array $overrides = []): Poll
    {
        $poll = Poll::create(array_merge([
            'question' => $question,
            'slug' => $slug,
            'poll_type' => Poll::TYPE_POLITICAL,
            'status' => Poll::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'audience_scope' => Poll::AUDIENCE_NATIONAL,
            'created_by' => $this->voter()->id,
        ], $overrides));

        foreach ($candidateIds as $index => $candidateId) {
            // The option label is what the poll card prints.
            PollOption::create([
                'poll_id' => $poll->id,
                'label' => Candidate::find($candidateId)?->name ?? 'Option '.($index + 1),
                'candidate_id' => $candidateId,
                'display_order' => $index + 1,
            ]);
        }

        return $poll;
    }

    public function test_polls_read_in_ballot_order(): void
    {
        // Created in the opposite order to the ballot, newest (MCA) first.
        $this->poll('MCA race in Kileleshwa', 'mca-race', [$this->candidate('Mca One', 'MCA')->id]);
        $this->poll('MP race in Dagoretti North', 'mp-race', [$this->candidate('Mp One', 'MP')->id]);
        $this->poll('Women Rep race', 'women-rep-race', [$this->candidate('Women One', 'Women Rep')->id]);
        $this->poll('Senate race', 'senate-race', [$this->candidate('Senator One', 'Senator')->id]);
        $this->poll('Governor race', 'governor-race', [$this->candidate('Governor One', 'Governor')->id]);
        $this->poll('Presidential race', 'presidential-race', [$this->candidate('President One', 'President')->id]);

        $this->actingAs($this->voter())
            ->get(route('polls.public'))
            ->assertOk()
            ->assertSeeInOrder([
                'Presidential race',
                'Governor race',
                'Senate race',
                'Women Rep race',
                'MP race in Dagoretti North',
                'MCA race in Kileleshwa',
            ]);
    }

    public function test_the_leader_tops_each_poll_and_zero_votes_sit_last(): void
    {
        $trailing = $this->candidate('Trailing Aspirant', 'Governor');
        $leader = $this->candidate('Leading Aspirant', 'Governor');
        $middle = $this->candidate('Middle Aspirant', 'Governor');

        // Defined in this order, but the tally decides the display.
        $poll = $this->poll('Who should be governor?', 'governor-poll', [
            $trailing->id,
            $leader->id,
            $middle->id,
        ], [
            // Closed with public results, so the tallies are on the page.
            'ends_at' => now()->subMinute(),
            'reveal_results' => true,
        ]);

        $options = $poll->options()->orderBy('display_order')->get();
        $voters = User::factory()->count(3)->create(['role' => 'user', 'role_id' => Role::idFor(Role::USER)]);

        // Leader gets 2 votes, middle 1, trailing none.
        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[1]->id, 'user_id' => $voters[0]->id]);
        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[1]->id, 'user_id' => $voters[1]->id]);
        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[2]->id, 'user_id' => $voters[2]->id]);

        Cache::flush();

        $this->actingAs($this->voter())
            ->get(route('polls.public'))
            ->assertOk()
            ->assertSeeInOrder([
                'Leading Aspirant',
                'Middle Aspirant',
                'Trailing Aspirant',
            ]);
    }

    public function test_a_hidden_tally_keeps_the_admins_order(): void
    {
        $first = $this->candidate('First Listed', 'Senator');
        $second = $this->candidate('Second Listed', 'Senator');

        $poll = $this->poll('Who should be senator?', 'senator-poll', [$first->id, $second->id], [
            // Still open, so nobody sees the standing yet.
            'ends_at' => now()->addWeek(),
            'reveal_results' => true,
        ]);

        $options = $poll->options()->orderBy('display_order')->get();
        $voter = User::factory()->create(['role' => 'user', 'role_id' => Role::idFor(Role::USER)]);
        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[1]->id, 'user_id' => $voter->id]);

        Cache::flush();

        $this->actingAs($this->voter())
            ->get(route('polls.public'))
            ->assertOk()
            ->assertSeeInOrder(['First Listed', 'Second Listed']);
    }

    public function test_voting_returns_to_the_polls_page(): void
    {
        $voter = $this->voter();
        $poll = $this->poll('Who should be governor?', 'governor-poll', [
            $this->candidate('Governor One', 'Governor')->id,
            $this->candidate('Governor Two', 'Governor')->id,
        ]);

        $optionId = $poll->options()->orderBy('display_order')->value('id');

        $this->actingAs($voter)
            ->post(route('poll.vote', $poll), ['option_id' => $optionId])
            ->assertRedirect(route('polls.public'));

        $this->assertDatabaseHas('poll_votes', ['poll_id' => $poll->id, 'poll_option_id' => $optionId]);
    }

    public function test_voting_from_a_poll_page_returns_to_that_page(): void
    {
        $voter = $this->voter();
        $poll = $this->poll('Who should be governor?', 'governor-poll', [
            $this->candidate('Governor One', 'Governor')->id,
            $this->candidate('Governor Two', 'Governor')->id,
        ]);

        $optionId = $poll->options()->orderBy('display_order')->value('id');
        $referer = route('poll.show', $poll->slug);

        $this->actingAs($voter)
            ->from($referer)
            ->post(route('poll.vote', $poll), ['option_id' => $optionId])
            ->assertRedirect($referer);
    }

    public function test_the_polls_page_hero_stays_compact(): void
    {
        $html = (string) $this->actingAs($this->voter())
            ->get(route('polls.public'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('padding: clamp(16px,3vw,30px) 0 0;', $html);
        $this->assertStringNotContainsString('padding: clamp(40px,8vw,90px) 0 0;', $html);
    }
}
