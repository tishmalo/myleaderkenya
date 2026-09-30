<?php

namespace Tests\Feature;

use App\Models\AspirantPoll;
use App\Models\Candidate;
use App\Models\Constituency;
use App\Models\County;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Position;
use App\Models\User;
use App\Services\Web\PublicApprovalService;
use App\Support\HomepageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PollFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The poll shell is cached for 60s, so each test needs a clean slate.
        HomepageCache::flush();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function voter(): User
    {
        return User::factory()->create(['role' => 'voter']);
    }

    /**
     * There is no CandidateFactory in this project, so approved aspirants are
     * created directly. `slug` is derived by the model; `position_id` is NOT
     * NULL in the schema, so a position is created on first use.
     */
    private function candidate(string $name): Candidate
    {
        return Candidate::create([
            'name' => $name,
            'approval_status' => 'approved',
            'position_id' => $this->positionId(),
        ]);
    }

    private function candidateAt(string $name, array $location = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'approval_status' => 'approved',
            'position_id' => $this->positionId(),
        ], $location));
    }

    private function governorAt(string $name, array $location = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'approval_status' => 'approved',
            'position_id' => $this->governorPositionId(),
        ], $location));
    }

    private function governorPositionId(): int
    {
        return Position::firstOrCreate(
            ['name' => 'Governor'],
            ['sort_order' => 2]
        )->id;
    }

    private function mpPositionId(): int
    {
        return Position::firstOrCreate(
            ['name' => 'Member of Parliament'],
            ['sort_order' => 3]
        )->id;
    }

    private function mcaPositionId(): int
    {
        return Position::firstOrCreate(
            ['name' => 'Member of County Assembly'],
            ['sort_order' => 4]
        )->id;
    }

    private function candidateWithPosition(string $name, int $positionId, array $location = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'approval_status' => 'approved',
            'position_id' => $positionId,
        ], $location));
    }

    private function positionId(): int
    {
        return Position::firstOrCreate(
            ['name' => 'Presidential Aspirant'],
            ['sort_order' => 1]
        )->id;
    }

    private function makePoll(array $overrides = []): Poll
    {
        return Poll::create(array_merge([
            'question' => 'Who should be our next president?',
            'slug' => 'who-should-be-our-next-president',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_ACTIVE,
            'ends_at' => now()->addDays(3),
            'reveal_results' => true,
            'created_by' => $this->admin()->id,
        ], $overrides));
    }

    private function makeCountyScopedPoll(string $question): Poll
    {
        $poll = $this->makePoll([
            'question' => $question,
            'slug' => Str::slug($question),
            'poll_type' => Poll::TYPE_POLITICAL,
            'audience_scope' => Poll::AUDIENCE_COUNTY,
            'audience_county' => 'Kirinyaga',
        ]);

        $poll->options()->create(['label' => 'Option A', 'display_order' => 0]);
        $poll->options()->create(['label' => 'Option B', 'display_order' => 1]);

        return $poll;
    }

    private function makeOptions(Poll $poll, array $labels = ['Option A', 'Option B']): void
    {
        foreach ($labels as $index => $label) {
            $poll->options()->create([
                'label' => $label,
                'display_order' => $index,
            ]);
        }
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'question' => 'Which county should host the next forum?',
            'poll_type' => 'words',
            'status' => 'active',
            'ends_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'reveal_results' => '1',
            'options' => [
                ['label' => 'Nakuru'],
                ['label' => 'Kisumu'],
            ],
        ], $overrides);
    }

    /* ------------------------------------------------------------------
       Admin
    ------------------------------------------------------------------ */

    public function test_admin_can_create_a_word_poll(): void
    {
        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload())
            ->assertRedirect(route('polls.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('polls', ['question' => 'Which county should host the next forum?']);
        $this->assertDatabaseHas('poll_options', ['label' => 'Nakuru']);
        $this->assertDatabaseHas('poll_options', ['label' => 'Kisumu']);
    }

    public function test_a_poll_needs_at_least_two_options(): void
    {
        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'options' => [['label' => 'Only one']],
            ]))
            ->assertSessionHasErrors('options');

        $this->assertDatabaseCount('polls', 0);
    }

    public function test_the_deadline_must_be_in_the_future(): void
    {
        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('polls', 0);
    }

    public function test_activating_a_poll_leaves_other_live_polls_alone(): void
    {
        $live = $this->makePoll();
        $this->makeOptions($live);

        $draft = Poll::create([
            'question' => 'Nakuru or Kisumu?',
            'slug' => 'nakuru-or-kisumu',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_DRAFT,
            'ends_at' => now()->addDays(2),
            'created_by' => $this->admin()->id,
        ]);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Mombasa or Eldoret?',
                'status' => 'active',
            ]))
            ->assertRedirect(route('polls.index'));

        // Once duplicate polls could be created, a second live poll no longer
        // demotes the first: the homepage shows every open poll in audience.
        $this->assertSame(Poll::STATUS_ACTIVE, $live->fresh()->status);

        // ...and the unrelated draft is left alone; it never was on the homepage.
        $this->assertSame(Poll::STATUS_DRAFT, $draft->fresh()->status);

        // Two polls can be live at the same time now.
        $this->assertSame(2, Poll::query()->where('status', Poll::STATUS_ACTIVE)->count());
    }

    public function test_creating_a_draft_poll_does_not_close_the_live_poll(): void
    {
        $live = $this->makePoll();
        $this->makeOptions($live);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload(['status' => 'draft']))
            ->assertRedirect(route('polls.index'));

        $this->assertSame(Poll::STATUS_ACTIVE, $live->fresh()->status);
    }

    public function test_admin_sees_results_even_before_the_deadline(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $options = $poll->options()->get();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[0]->id, 'user_id' => $this->voter()->id]);

        $this->assertFalse($poll->hasPublicResults());

        $this->actingAs($this->admin())
            ->get(route('polls.edit', $poll))
            ->assertOk()
            ->assertSee('Results')
            ->assertSee('Option A');
    }

    /**
     * The create form is passed a null poll. old() evaluates its arguments
     * eagerly, so reading $poll->options as a default used to throw and 500
     * the whole page. Both the blank create screen and the screen redisplayed
     * with old() input after a failed submit have to render.
     */
    public function test_the_create_page_renders_with_a_null_poll(): void
    {
        $this->actingAs($this->admin())
            ->get(route('polls.create'))
            ->assertOk()
            ->assertSee('Create Poll');
    }

    public function test_the_create_page_renders_again_with_old_input_after_a_failed_submit(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('polls.create'))
            ->post(route('polls.store'), [
                'question' => '',
                'options' => [['label' => 'Option A'], ['label' => 'Option B']],
            ])
            ->assertRedirect(route('polls.create'))
            ->assertSessionHasErrors('question');

        $this->actingAs($admin)
            ->get(route('polls.create'))
            ->assertOk()
            ->assertSee('Option A');
    }

    public function test_the_results_endpoint_returns_the_live_tally(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $options = $poll->options()->get();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[0]->id, 'user_id' => $this->voter()->id]);

        $this->actingAs($this->admin())
            ->getJson(route('polls.results', $poll))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('results.0.votes', 1)
            ->assertJsonPath('results.0.percent', 100)
            ->assertJsonPath('results.1.votes', 0);
    }

    public function test_a_poll_needs_a_slug_that_does_not_collide(): void
    {
        $this->makePoll(['question' => 'Same question', 'slug' => 'same-question']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload(['question' => 'Same question']))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', ['slug' => 'same-question-2']);
    }

    public function test_admin_can_delete_a_poll(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->actingAs($this->admin())
            ->deleteJson(route('polls.destroy', $poll))
            ->assertOk();

        $this->assertDatabaseCount('polls', 0);
        $this->assertDatabaseCount('poll_options', 0);
    }

    /* ------------------------------------------------------------------
       Political options
    ------------------------------------------------------------------ */

    public function test_a_political_poll_takes_its_labels_from_the_linked_aspirants(): void
    {
        $candidate = $this->candidate('Martha Karua');

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who is your preferred candidate?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => $candidate->id, 'label' => 'ignored'],
                    ['candidate_id' => null, 'label' => null],
                ],
            ]))
            ->assertSessionHasErrors('options');

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who is your preferred candidate two?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => $candidate->id, 'label' => 'ignored'],
                    ['label' => 'None of the above'],
                    ['label' => 'Undecided'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('poll_options', [
            'label' => 'Martha Karua',
            'candidate_id' => $candidate->id,
        ]);
    }

    /* ------------------------------------------------------------------
       Bulk aspirant picker
    ------------------------------------------------------------------ */

    public function test_the_aspirant_picker_rejects_guests(): void
    {
        $this->getJson(route('polls.aspirants'))->assertUnauthorized();
    }

    public function test_the_aspirant_picker_returns_locations_and_matching_aspirants(): void
    {
        $county = County::create(['name' => 'Kirinyaga']);
        Constituency::create(['name' => 'Kirinyaga Central', 'county_id' => $county->id]);

        $this->governorAt('Jane Nduku', ['county' => 'Kirinyaga', 'constituency' => 'Kirinyaga Central']);
        $this->governorAt('Martha Karua', ['county' => 'Kirinyaga']);
        $this->governorAt('Sam Waweru', ['county' => 'Nairobi']);

        $governorPositionId = $this->governorPositionId();
        $this->actingAs($this->admin())
            ->getJson(route('polls.aspirants', [
                'position_id' => $governorPositionId,
                'county' => 'Kirinyaga',
            ]))
            ->assertOk()
            ->assertJsonPath('constituencies.0', 'Kirinyaga Central')
            ->assertJsonCount(2, 'candidates')
            ->assertJsonPath('candidates.0.name', 'Jane Nduku');
    }

    public function test_the_aspirant_picker_refuses_to_dump_every_aspirant(): void
    {
        $this->candidate('Martha Karua');

        $this->actingAs($this->admin())
            ->getJson(route('polls.aspirants'))
            ->assertOk()
            ->assertJsonCount(0, 'candidates');
    }

    public function test_an_admin_can_add_many_aspirants_in_one_submit(): void
    {
        $a = $this->candidateAt('Martha Karua', ['county' => 'Kirinyaga']);
        $b = $this->candidateAt('Jane Nduku', ['county' => 'Kirinyaga']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who wins the county?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => $a->id, 'label' => 'Martha Karua'],
                    ['candidate_id' => $b->id, 'label' => 'Jane Nduku'],
                    ['label' => 'None of the above'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseCount('poll_options', 3);
        $this->assertDatabaseHas('poll_options', [
            'label' => 'Martha Karua',
            'candidate_id' => $a->id,
        ]);
        $this->assertDatabaseHas('poll_options', [
            'label' => 'Jane Nduku',
            'candidate_id' => $b->id,
        ]);
    }

    /* ------------------------------------------------------------------
       Audience scoping
    ------------------------------------------------------------------ */

    public function test_a_county_race_is_scoped_to_the_shared_county(): void
    {
        $this->governorAt('Jane Nduku', ['county' => 'Kirinyaga', 'constituency' => 'Kirinyaga Central']);
        $this->governorAt('Martha Karua', ['county' => 'Kirinyaga']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who wins Kirinyaga?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => 1, 'label' => 'Jane Nduku'],
                    ['candidate_id' => 2, 'label' => 'Martha Karua'],
                    ['label' => 'None of the above'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'question' => 'Who wins Kirinyaga?',
            'audience_scope' => Poll::AUDIENCE_COUNTY,
            'audience_county' => 'Kirinyaga',
            'audience_constituency' => null,
            'audience_ward' => null,
        ]);
    }

    public function test_an_mp_race_is_scoped_to_the_shared_constituency(): void
    {
        $this->candidateWithPosition('A', $this->mpPositionId(), ['county' => 'Kirinyaga', 'constituency' => 'Ndia', 'ward' => 'Kariti']);
        $this->candidateWithPosition('B', $this->mpPositionId(), ['county' => 'Kirinyaga', 'constituency' => 'Ndia', 'ward' => 'Kariti']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who wins Ndia?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => 1, 'label' => 'A'],
                    ['candidate_id' => 2, 'label' => 'B'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'question' => 'Who wins Ndia?',
            'audience_scope' => Poll::AUDIENCE_CONSTITUENCY,
            'audience_county' => 'Kirinyaga',
            'audience_constituency' => 'Ndia',
            'audience_ward' => null,
        ]);
    }

    public function test_an_mca_race_is_scoped_to_the_shared_ward(): void
    {
        $this->candidateWithPosition('A', $this->mcaPositionId(), ['county' => 'Kirinyaga', 'constituency' => 'Ndia', 'ward' => 'Kariti']);
        $this->candidateWithPosition('B', $this->mcaPositionId(), ['county' => 'Kirinyaga', 'constituency' => 'Ndia', 'ward' => 'Kariti']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who wins Kariti?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => 1, 'label' => 'A'],
                    ['candidate_id' => 2, 'label' => 'B'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'question' => 'Who wins Kariti?',
            'audience_scope' => Poll::AUDIENCE_WARD,
            'audience_county' => 'Kirinyaga',
            'audience_constituency' => 'Ndia',
            'audience_ward' => 'Kariti',
        ]);
    }

    public function test_a_presidential_race_is_national(): void
    {
        $this->candidate('Martha Karua');
        $this->candidate('Raila Odinga');

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'Who is our next president?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => 1, 'label' => 'Martha Karua'],
                    ['candidate_id' => 2, 'label' => 'Raila Odinga'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'question' => 'Who is our next president?',
            'audience_scope' => Poll::AUDIENCE_NATIONAL,
            'audience_county' => null,
        ]);
    }

    public function test_aspirants_spanning_counties_fall_back_to_members(): void
    {
        $a = $this->candidateWithPosition('A', $this->governorPositionId(), ['county' => 'Kirinyaga']);
        $b = $this->candidateWithPosition('B', $this->governorPositionId(), ['county' => 'Nairobi']);

        $this->actingAs($this->admin())
            ->post(route('polls.store'), $this->payload([
                'question' => 'A very wide race',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => $a->id, 'label' => 'A'],
                    ['candidate_id' => $b->id, 'label' => 'B'],
                ],
            ]))
            ->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'question' => 'A very wide race',
            'audience_scope' => Poll::AUDIENCE_MEMBERS,
            'audience_county' => null,
        ]);
    }

    public function test_the_audience_is_recomputed_when_editing_aspirants(): void
    {
        Poll::create([
            'question' => 'Who wins Kirinyaga?',
            'slug' => 'who-wins-kirinyaga',
            'poll_type' => Poll::TYPE_POLITICAL,
            'status' => Poll::STATUS_DRAFT,
            'ends_at' => now()->addDays(3),
            'created_by' => $this->admin()->id,
            'audience_scope' => Poll::AUDIENCE_COUNTY,
            'audience_county' => 'Kirinyaga',
        ]);

        $a = $this->candidate('Martha Karua');
        $b = $this->candidate('Raila Odinga');

        $this->actingAs($this->admin())
            ->put(route('polls.update', ['poll' => 1]), $this->payload([
                'question' => 'Who wins Kirinyaga?',
                'poll_type' => 'political',
                'options' => [
                    ['candidate_id' => $a->id, 'label' => 'Martha Karua'],
                    ['candidate_id' => $b->id, 'label' => 'Raila Odinga'],
                ],
            ]))
            ->assertRedirect(route('polls.edit', ['poll' => 1]));

        $this->assertDatabaseHas('polls', [
            'question' => 'Who wins Kirinyaga?',
            'audience_scope' => Poll::AUDIENCE_NATIONAL,
            'audience_county' => null,
        ]);
    }

    /* ------------------------------------------------------------------
       Voting
    ------------------------------------------------------------------ */

    public function test_a_signed_in_voter_can_vote(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $option = $poll->options()->first();
        $user = $this->voter();

        $this->actingAs($user)
            ->post(route('poll.vote', $poll), ['option_id' => $option->id])
            ->assertRedirect(route('landing'))
            ->assertSessionHas('poll_notice');

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_second_vote_replaces_the_first(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $options = $poll->options()->get();
        $user = $this->voter();

        $this->actingAs($user)->post(route('poll.vote', $poll), ['option_id' => $options[0]->id]);
        $this->actingAs($user)->post(route('poll.vote', $poll), ['option_id' => $options[1]->id]);

        $this->assertDatabaseCount('poll_votes', 1);
        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $options[1]->id,
        ]);
    }

    public function test_a_vote_must_target_an_option_on_this_poll(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $other = $this->makePoll(['question' => 'Another', 'slug' => 'another']);
        $otherOption = $other->options()->create(['label' => 'Elsewhere', 'display_order' => 0]);

        $this->actingAs($this->voter())
            ->post(route('poll.vote', $poll), ['option_id' => $otherOption->id])
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_voting_is_refused_once_the_deadline_has_passed(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->post(route('poll.vote', $poll), ['option_id' => $poll->options()->first()->id])
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_voting_is_refused_before_the_poll_opens(): void
    {
        $poll = $this->makePoll(['starts_at' => now()->addDay()]);
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->post(route('poll.vote', $poll), ['option_id' => $poll->options()->first()->id])
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_guests_cannot_vote(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->post(route('poll.vote', $poll), ['option_id' => $poll->options()->first()->id])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_a_draft_poll_is_not_votable(): void
    {
        $poll = $this->makePoll(['status' => Poll::STATUS_DRAFT]);
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->post(route('poll.vote', $poll), ['option_id' => $poll->options()->first()->id])
            ->assertNotFound();
    }

    /* ------------------------------------------------------------------
       Result visibility on the homepage
    ------------------------------------------------------------------ */

    public function test_results_are_hidden_from_the_public_before_the_deadline(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $options = $poll->options()->get();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[0]->id, 'user_id' => $this->voter()->id]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee($poll->question)
            ->assertSee('Results hidden')
            ->assertDontSee('poll-option-tally');
    }

    public function test_results_are_public_once_the_deadline_passes(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);
        $options = $poll->options()->get();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[0]->id, 'user_id' => $this->voter()->id]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('poll-option-tally')
            ->assertSee('100%')
            ->assertDontSee('Results hidden');
    }

    public function test_results_stay_hidden_forever_when_reveal_is_off(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute(), 'reveal_results' => false]);
        $this->makeOptions($poll);
        $options = $poll->options()->get();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $options[0]->id, 'user_id' => $this->voter()->id]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Results hidden')
            ->assertDontSee('poll-option-tally');
    }

    public function test_no_poll_section_when_there_is_no_active_poll(): void
    {
        $this->makePoll(['status' => Poll::STATUS_DRAFT]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertDontSee('poll-section')
            ->assertDontSee('Who should be our next president?');
    }

    public function test_only_the_active_poll_is_shown(): void
    {
        $active = $this->makePoll();
        $this->makeOptions($active);

        $closed = Poll::create([
            'question' => 'An old closed poll',
            'slug' => 'an-old-closed-poll',
            'poll_type' => Poll::TYPE_WORDS,
            'status' => Poll::STATUS_CLOSED,
            'ends_at' => now()->subWeek(),
            'created_by' => $this->admin()->id,
        ]);
        $closed->options()->create(['label' => 'Old option', 'display_order' => 0]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee($active->question)
            ->assertDontSee('An old closed poll');
    }

    public function test_a_guest_only_see_national_polls(): void
    {
        $national = $this->makePoll(['question' => 'National question', 'slug' => 'national-question']);
        $this->makeOptions($national);

        $county = $this->makeCountyScopedPoll('Who wins Kirinyaga?');

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('National question')
            ->assertDontSee('Who wins Kirinyaga?');
    }

    public function test_a_member_sees_only_their_countys_poll(): void
    {
        $county = $this->makeCountyScopedPoll('Who wins Kirinyaga?');

        $resident = User::factory()->create(['role' => 'voter', 'county' => 'Kirinyaga', 'constituency' => 'Kirinyaga Central', 'ward' => 'Kariti']);
        $outsider = User::factory()->create(['role' => 'voter', 'county' => 'Nairobi']);

        $this->actingAs($resident)->get(route('landing'))
            ->assertOk()
            ->assertSee('Who wins Kirinyaga?');

        $this->actingAs($outsider)->get(route('landing'))
            ->assertOk()
            ->assertDontSee('Who wins Kirinyaga?')->assertOk();
    }

    public function test_a_member_without_a_location_sees_no_local_poll_and_is_prompted(): void
    {
        $this->makeCountyScopedPoll('Who wins Kirinyaga?');

        $member = $this->voter();

        $this->actingAs($member)
            ->get(route('landing'))
            ->assertOk()
            ->assertDontSee('Who wins Kirinyaga?')
            ->assertSee('Set your location');
    }

    public function test_a_member_in_the_right_location_can_vote_on_a_local_poll(): void
    {
        $poll = $this->makeCountyScopedPoll('Who wins Kirinyaga?');
        $option = $poll->options()->first();

        $resident = User::factory()->create(['role' => 'voter', 'county' => 'Kirinyaga']);

        $this->actingAs($resident)
            ->post(route('poll.vote', $poll), ['option_id' => $option->id])
            ->assertRedirect(route('landing'))
            ->assertSessionHas('poll_notice');

        $this->assertDatabaseHas('poll_votes', ['poll_id' => $poll->id, 'user_id' => $resident->id]);
    }

    public function test_a_member_outside_the_area_cannot_vote_on_a_local_poll(): void
    {
        $poll = $this->makeCountyScopedPoll('Who wins Kirinyaga?');
        $option = $poll->options()->first();

        $outsider = User::factory()->create(['role' => 'voter', 'county' => 'Nairobi']);

        $this->actingAs($outsider)
            ->post(route('poll.vote', $poll), ['option_id' => $option->id])
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_a_member_cannot_vote_on_a_constituency_poll_they_are_not_in(): void
    {
        $poll = $this->makePoll([
            'question' => 'Who wins Ndia?',
            'slug' => 'who-wins-ndia',
            'audience_scope' => Poll::AUDIENCE_CONSTITUENCY,
            'audience_county' => 'Kirinyaga',
            'audience_constituency' => 'Ndia',
        ]);
        $this->makeOptions($poll);

        $wrongWard = User::factory()->create(['role' => 'voter', 'county' => 'Kirinyaga', 'constituency' => 'Kirinyaga Central']);

        $this->actingAs($wrongWard)
            ->post(route('poll.vote', $poll), ['option_id' => $poll->options()->first()->id])
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseCount('poll_votes', 0);
    }

    public function test_the_poll_sits_above_the_public_pulse_section(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        // The pulse section is wrapped in @if(! empty($publicApprovalCards)),
        // so it only renders when the service returns cards. Stub it so the
        // ordering between the two sections can actually be asserted.
        $this->instance(PublicApprovalService::class, new class extends PublicApprovalService
        {
            public function __construct() {}

            public function presidentialCards(): array
            {
                return [[
                    'name' => 'Pulse Candidate',
                    'portrait_url' => 'https://example.test/pulse.png',
                    'approval' => 55.5,
                    'direction' => 'up',
                    'confidence' => 'high',
                    'theme' => 'positive',
                ]];
            }
        });

        $html = $this->get(route('landing'))->assertOk()->getContent();

        $pollPosition = strpos($html, 'poll-section');
        $pulsePosition = strpos($html, 'public-approval-section');

        $this->assertNotFalse($pollPosition, 'The poll section is missing.');
        $this->assertNotFalse($pulsePosition, 'The public pulse section is missing.');
        $this->assertLessThan($pulsePosition, $pollPosition, 'The poll must render above Public Sentiment.');
    }

    public function test_a_voter_is_told_their_vote_was_recorded(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);
        $user = $this->voter();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $poll->options()->first()->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('landing'))
            ->assertOk()
            ->assertSee('Vote recorded');
    }

    /* ------------------------------------------------------------------
       Comments
    ------------------------------------------------------------------ */

    public function test_a_voter_can_comment_and_the_comment_starts_as_pending(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);
        $user = $this->voter();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $poll->options()->first()->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('poll.comments.store', $poll), ['body' => 'I voted for Option A.'])
            ->assertRedirect(route('landing'))
            ->assertSessionHas('poll_comment_notice');

        $this->assertDatabaseHas('poll_comments', [
            'poll_id' => $poll->id,
            'user_id' => $user->id,
            'body' => 'I voted for Option A.',
            'status' => PollComment::STATUS_PENDING,
        ]);
    }

    public function test_commenting_requires_a_prior_vote(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->post(route('poll.comments.store', $poll), ['body' => 'I have not voted yet.'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('poll_comments', 0);
    }

    public function test_guests_cannot_comment(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);

        $this->post(route('poll.comments.store', $poll), ['body' => 'Anonymous comment.'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('poll_comments', 0);
    }

    public function test_pending_comments_are_hidden_from_the_public(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute()]);
        $this->makeOptions($poll);

        $poll->comments()->create([
            'user_id' => $this->voter()->id,
            'body' => 'An approved remark.',
            'status' => PollComment::STATUS_APPROVED,
        ]);
        $poll->comments()->create([
            'user_id' => $this->voter()->id,
            'body' => 'A pending remark.',
            'status' => PollComment::STATUS_PENDING,
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('An approved remark.')
            ->assertDontSee('A pending remark.');
    }

    public function test_an_admin_can_approve_a_poll_comment(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $comment = $poll->comments()->create([
            'user_id' => $this->voter()->id,
            'body' => 'Please approve me.',
            'status' => PollComment::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->putJson(route('poll-comments.update', $comment), ['status' => PollComment::STATUS_APPROVED])
            ->assertOk();

        $this->assertSame(PollComment::STATUS_APPROVED, $comment->fresh()->status);
        $this->assertNotNull($comment->fresh()->moderated_by);
        $this->assertNotNull($comment->fresh()->moderated_at);
    }

    public function test_the_comment_moderation_queue_can_be_filtered(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $poll->comments()->create([
            'user_id' => $this->voter()->id,
            'body' => 'Waiting for review.',
            'status' => PollComment::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->get(route('poll-comments.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Waiting for review.');

        $this->actingAs($this->admin())
            ->get(route('poll-comments.index', ['status' => 'approved']))
            ->assertOk()
            ->assertDontSee('Waiting for review.');
    }

    /* ------------------------------------------------------------------
       The pre-existing aspirant poll feature must be untouched
    ------------------------------------------------------------------ */

    public function test_the_existing_aspirant_poll_tables_are_untouched(): void
    {
        $this->assertTrue(Schema::hasTable('aspirant_polls'));
        $this->assertTrue(Schema::hasTable('aspirant_poll_responses'));
        $this->assertTrue(Schema::hasColumns('aspirant_polls', [
            'question',
            'options',
            'status',
            'published_at',
        ]));
    }

    public function test_the_aspirant_poll_model_and_api_route_are_unchanged(): void
    {
        $aspirant = $this->candidate('Legacy Aspirant');
        $user = $this->voter();

        $poll = AspirantPoll::create([
            'candidate_id' => $aspirant->id,
            'user_id' => $user->id,
            'question' => 'Existing aspirant poll?',
            'options' => ['Yes', 'No'],
            'scope_type' => 'group',
            'status' => 'published',
        ]);

        $this->assertNotNull($poll->fresh());
        $this->assertSame('Yes', $poll->options[0]);

        // The pre-existing aspirant opinion-polls endpoint must still resolve
        // to the same URI; route() returns a URL, so compare the path.
        $this->assertStringEndsWith(
            '/aspirant/tools/opinion-polls/polls',
            route('aspirant.tools.polls.store')
        );
    }

    public function test_the_new_poll_tables_do_not_collide_with_the_aspirant_ones(): void
    {
        $this->assertNotSame('aspirant_polls', (new Poll)->getTable());
        $this->assertNotSame('aspirant_poll_responses', (new PollVote)->getTable());
        $this->assertNotSame('aspirant_polls', (new PollOption)->getTable());
    }

    /**
     * The comment form is only rendered after voting closes, so the endpoint
     * has to refuse the same states on a direct POST.
     */
    public function test_commenting_is_refused_while_the_poll_is_still_open(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->addDay()]);
        $this->makeOptions($poll);
        $user = $this->voter();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $poll->options()->first()->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->from(route('landing'))
            ->post(route('poll.comments.store', $poll), ['body' => 'Too early for this.'])
            ->assertRedirect(route('landing'))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('poll_comments', 0);
    }

    public function test_commenting_is_refused_when_results_stay_hidden(): void
    {
        $poll = $this->makePoll(['ends_at' => now()->subMinute(), 'reveal_results' => false]);
        $this->makeOptions($poll);
        $user = $this->voter();

        PollVote::create(['poll_id' => $poll->id, 'poll_option_id' => $poll->options()->first()->id, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->from(route('landing'))
            ->post(route('poll.comments.store', $poll), ['body' => 'Results stay hidden.'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('poll_comments', 0);
    }

    public function test_a_scheduled_poll_is_described_as_opening_not_closed(): void
    {
        $poll = $this->makePoll([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);
        $this->makeOptions($poll);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Voting opens')
            ->assertDontSee('This poll closed');
    }

    public function test_guests_get_no_vote_form_on_the_homepage(): void
    {
        $this->makeOptions($this->makePoll());

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('poll-grid')
            ->assertDontSee('name="option_id"')
            ->assertDontSee('Cast your vote');
    }

    public function test_vote_copy_allows_changing_a_vote_before_the_deadline(): void
    {
        // The service updates the existing row rather than rejecting, so the
        // hint must not promise the vote is final.
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->get(route('landing'))
            ->assertOk()
            ->assertSee('You can change it until the poll closes')
            ->assertDontSee('You cannot change it once cast');
    }

    public function test_a_active_poll_is_reachable_by_slug_with_share_actions(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->get(route('poll.show', $poll->slug))
            ->assertOk()
            ->assertSee($poll->question)
            ->assertSee('Share this poll')
            ->assertSee('wa.me')
            ->assertSee('twitter.com/intent/tweet')
            ->assertSee('facebook.com/sharer/sharer.php')
            ->assertSee('data-poll-copy-link')
            ->assertDontSee('class="poll-submit"');
    }

    public function test_share_page_carries_seo_metadata(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->get(route('poll.show', $poll->slug))
            ->assertOk()
            ->assertSee('og:title')
            ->assertSee('og:description')
            ->assertSee('og:image')
            ->assertSee('twitter:card')
            ->assertSee('application/ld+json')
            ->assertSee('Article')
            ->assertSee('rel="canonical"', false);
    }

    public function test_a_draft_or_unknown_poll_slug_returns_not_found(): void
    {
        $draft = $this->makePoll(['status' => Poll::STATUS_DRAFT]);
        $this->makeOptions($draft);

        $this->get(route('poll.show', $draft->slug))->assertNotFound();

        $this->get(route('poll.show', 'no-such-poll'))->assertNotFound();
    }

    public function test_a_voter_can_vote_from_the_share_page(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->actingAs($this->voter())
            ->get(route('poll.show', $poll->slug))
            ->assertOk()
            ->assertSee('Cast your vote');

        $this->post(route('poll.vote', $poll->id), ['option_id' => $poll->options->first()->id])
            ->assertRedirect(route('landing'));

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $poll->options->first()->id,
        ]);
    }

    public function test_the_homepage_offers_a_share_link_for_the_active_poll(): void
    {
        $poll = $this->makePoll();
        $this->makeOptions($poll);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Share this poll')
            ->assertSee(route('poll.show', $poll->slug))
            ->assertSee('data-poll-copy-link');
    }
}
