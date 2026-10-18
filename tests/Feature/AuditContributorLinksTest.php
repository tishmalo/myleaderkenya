<?php

namespace Tests\Feature;

use App\Contracts\Repositories\Audit\AuditRepositoryInterface;
use App\Models\Audit;
use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\Position;
use App\Models\ResourceLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The contributor summary on the audit trail credits each actor with the links
 * they added, alongside the candidates they created.
 */
class AuditContributorLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The auditing package stays silent in the console, and PHPUnit runs in
        // the console, so switch it on for these tests.
        config(['audit.console' => true]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function actor(string $name, string $username): User
    {
        return User::factory()->create([
            'name' => $name,
            'username' => $username,
            'role' => 'user',
            'role_id' => Role::idFor(Role::USER),
        ]);
    }

    private function county(): County
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);

        return County::create(['name' => 'Nairobi', 'bloc_id' => $bloc->id]);
    }

    private function candidate(string $name, User $creator): Candidate
    {
        $this->actingAs($creator);

        return Candidate::create([
            'name' => $name,
            'position_id' => Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor'])->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'approval_status' => 'approved',
        ]);
    }

    private function link(string $title, User $creator, County $county): ResourceLink
    {
        $this->actingAs($creator);

        return ResourceLink::create([
            'user_id' => $creator->id,
            'platform' => 'facebook_group',
            'title' => $title,
            'url' => 'https://www.facebook.com/groups/'.$title,
            'county_id' => $county->id,
        ]);
    }

    private function summaryByActor(array $filters = []): array
    {
        return app(AuditRepositoryInterface::class)
            ->candidateCreatorSummary($filters)
            ->mapWithKeys(fn ($row) => [$row->actor_name => (int) $row->links_added])
            ->all();
    }

    public function test_links_outside_the_selected_date_range_are_left_out(): void
    {
        $alice = $this->actor('Alice Actor', 'alice-actor');
        $county = $this->county();

        $this->candidate('Alice aspirant', $alice);

        $older = $this->link('alice-old', $alice, $county);
        $this->link('alice-fresh', $alice, $county);

        // Push only the older link outside today's window.
        Audit::query()
            ->where('auditable_type', (new ResourceLink())->getMorphClass())
            ->where('auditable_id', $older->id)
            ->update(['created_at' => now()->subDays(10)]);

        $this->assertSame(['Alice Actor' => 2], $this->summaryByActor());

        $today = now()->toDateString();

        $this->assertSame(
            ['Alice Actor' => 1],
            $this->summaryByActor(['date_from' => $today, 'date_to' => $today])
        );
    }

    public function test_links_added_counts_the_links_each_actor_created(): void
    {
        $alice = $this->actor('Alice Actor', 'alice-actor');
        $bob = $this->actor('Bob Actor', 'bob-actor');
        $county = $this->county();

        $this->candidate('Alice aspirant', $alice);
        $this->candidate('Bob aspirant', $bob);

        $this->link('alice-one', $alice, $county);
        $this->link('alice-two', $alice, $county);
        $this->link('alice-three', $alice, $county);
        $this->link('bob-one', $bob, $county);

        $this->assertSame([
            'Alice Actor' => 3,
            'Bob Actor' => 1,
        ], $this->summaryByActor());
    }

    public function test_a_link_deleted_afterwards_still_credits_its_creator(): void
    {
        $alice = $this->actor('Alice Actor', 'alice-actor');
        $county = $this->county();

        $this->candidate('Alice aspirant', $alice);

        $kept = $this->link('alice-kept', $alice, $county);
        $removed = $this->link('alice-removed', $alice, $county);

        $this->actingAs($alice);
        $removed->delete();

        $this->assertNull(ResourceLink::query()->find($removed->id));
        $this->assertSame(['Alice Actor' => 2], $this->summaryByActor());
    }

    public function test_the_page_shows_a_links_added_column_per_actor(): void
    {
        $admin = $this->superAdmin();
        $alice = $this->actor('Alice Actor', 'alice-actor');
        $bob = $this->actor('Bob Actor', 'bob-actor');
        $county = $this->county();

        $this->candidate('Alice aspirant', $alice);
        $this->candidate('Bob aspirant', $bob);

        foreach (range(1, 7) as $index) {
            $this->link('alice-'.$index, $alice, $county);
        }

        $this->link('bob-one', $bob, $county);

        $response = $this->actingAs($admin)->get(route('audits.index'))->assertOk();

        $response->assertSee('Links added');

        $table = $this->contributorTable((string) $response->getContent());

        $aliceRow = $this->rowFor($table, 'Alice Actor');
        $bobRow = $this->rowFor($table, 'Bob Actor');

        $this->assertStringContainsString('>7<', $aliceRow);
        $this->assertStringContainsString('>1<', $bobRow);
    }

    private function contributorTable(string $html): string
    {
        $start = strpos($html, 'candidate-contributors-title');

        $this->assertNotFalse($start, 'The contributor summary is missing from the audit page.');

        return substr($html, (int) $start);
    }

    private function rowFor(string $table, string $actorName): string
    {
        foreach (explode('<tr', $table) as $row) {
            if (str_contains($row, $actorName)) {
                return $row;
            }
        }

        $this->fail("No contributor row found for {$actorName}.");
    }
}
