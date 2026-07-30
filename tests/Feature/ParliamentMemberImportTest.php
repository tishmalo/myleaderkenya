<?php

namespace Tests\Feature;

use App\Jobs\FetchParliamentMemberDetail;
use App\Jobs\VerifyMissingParliamentMemberDetails;
use App\Models\Candidate;
use App\Models\ParliamentMember;
use App\Models\Position;
use App\Services\Parliament\ParliamentMemberImportService;
use App\Services\Parliament\ParliamentMemberMatcher;
use App\Services\Parliament\ParliamentMembersApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ParliamentMemberImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reordered_three_token_name_is_linked_automatically(): void
    {
        $position = Position::create(['name' => 'Member of Parliament', 'sort_order' => 1]);
        $candidate = Candidate::create(['name' => 'Alice Wambui Nganga', 'position_id' => $position->id, 'approval_status' => 'approved']);
        $member = ParliamentMember::create(['external_slug' => 'nganga-alice-wambui', 'source_name' => 'Nganga Alice Wambui', 'normalized_name' => 'alice nganga wambui']);

        app(ParliamentMemberMatcher::class)->match($member);

        $this->assertSame($candidate->id, $member->fresh()->candidate_id);
        $this->assertSame('automatic', $member->fresh()->match_method);
        $this->assertSame(3, $member->fresh()->matched_token_count);
    }

    public function test_two_token_and_ambiguous_names_are_not_linked(): void
    {
        $position = Position::create(['name' => 'Senator', 'sort_order' => 1]);
        Candidate::create(['name' => 'Alice Wambui', 'position_id' => $position->id]);
        Candidate::create(['name' => 'Alice Wambui Nganga', 'position_id' => $position->id]);
        Candidate::create(['name' => 'Nganga Alice Wambui', 'position_id' => $position->id]);
        $two = ParliamentMember::create(['external_slug' => 'alice-wambui', 'source_name' => 'Alice Wambui', 'normalized_name' => 'alice wambui']);
        $ambiguous = ParliamentMember::create(['external_slug' => 'alice-wambui-nganga', 'source_name' => 'Alice Wambui Nganga', 'normalized_name' => 'alice nganga wambui']);

        $matcher = app(ParliamentMemberMatcher::class);
        $matcher->match($two); $matcher->match($ambiguous);

        $this->assertNull($two->fresh()->candidate_id);
        $this->assertNull($ambiguous->fresh()->candidate_id);
        $this->assertSame('ambiguous', $ambiguous->fresh()->match_method);
    }

    public function test_detail_import_is_idempotent_and_preserves_manual_publication_controls(): void
    {
        $position = Position::create(['name' => 'Senator', 'sort_order' => 1]);
        $candidate = Candidate::create(['name' => 'Manual Candidate Link', 'position_id' => $position->id]);
        $member = ParliamentMember::create(['external_slug' => 'source-member', 'source_name' => 'Source Member', 'normalized_name' => 'member source', 'candidate_id' => $candidate->id, 'match_method' => 'manual', 'is_published' => true]);
        $payload = ['data' => ['name' => 'Source Member', 'party' => 'Example Party', 'committees' => ['Committee One'], 'positions' => ['Member of the 13th Parliament'], 'voting_patterns' => [['date' => 'July 1, 2025', 'title' => 'Example Bill', 'decision' => 'Yes']]]];

        $service = app(ParliamentMemberImportService::class);
        $service->importDetail($member, $payload); $service->importDetail($member->fresh(), $payload);

        $member->refresh();
        $this->assertSame($candidate->id, $member->candidate_id);
        $this->assertSame('manual', $member->match_method);
        $this->assertTrue($member->is_published);
        $this->assertSame('complete', $member->detail_status);
        $this->assertCount(1, $member->committees);
        $this->assertCount(2, $member->activities);
    }

    public function test_api_client_adds_bearer_token_but_exposes_only_safe_errors(): void
    {
        config()->set('services.parliament_members.base_url', 'https://members.test');
        config()->set('services.parliament_members.token', 'private-test-token');
        Http::fake(['https://members.test/members' => Http::response(['success' => true, 'data' => []])]);
        app(ParliamentMembersApiClient::class)->members();
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer private-test-token'));

        Http::fake(['https://members.test/members' => Http::response(['secret_body' => 'must-not-leak'], 500)]);
        try { app(ParliamentMembersApiClient::class)->members(); $this->fail('Expected API failure.'); }
        catch (RuntimeException $exception) { $this->assertSame('Parliament members API request failed.', $exception->getMessage()); $this->assertStringNotContainsString('must-not-leak', $exception->getMessage()); }
    }

    public function test_public_profile_shows_only_completed_and_published_linked_data(): void
    {
        $position = Position::create(['name' => 'Member of Parliament', 'sort_order' => 1]);
        $candidate = Candidate::create(['name' => 'Published Parliament Candidate', 'position_id' => $position->id, 'approval_status' => 'approved']);
        $member = ParliamentMember::create([
            'external_slug' => 'published-parliament-candidate',
            'source_name' => 'Published Parliament Candidate',
            'normalized_name' => 'candidate parliament published',
            'candidate_id' => $candidate->id,
            'detail_status' => 'complete',
            'biography' => 'Parliamentary biography sentinel.',
            'is_published' => false,
        ]);

        $this->get(route('aspirants.show', $candidate))->assertOk()->assertDontSee('Parliamentary biography sentinel.');
        $member->update(['is_published' => true, 'published_at' => now()]);
        $this->get(route('aspirants.show', $candidate->fresh()))->assertOk()->assertSee('Parliamentary biography sentinel.');
    }
    public function test_sunday_verifier_dispatches_only_missing_or_failed_details(): void
    {
        Bus::fake();
        $complete = ParliamentMember::create(['external_slug'=>'complete','source_name'=>'Complete Member','normalized_name'=>'complete member','detail_status'=>'complete']);
        $missing = ParliamentMember::create(['external_slug'=>'missing','source_name'=>'Missing Member','normalized_name'=>'member missing','detail_status'=>'missing']);
        $failed = ParliamentMember::create(['external_slug'=>'failed','source_name'=>'Failed Member','normalized_name'=>'failed member','detail_status'=>'failed']);

        (new VerifyMissingParliamentMemberDetails)->handle();

        Bus::assertNotDispatched(FetchParliamentMemberDetail::class, fn ($job): bool => $job->memberId === $complete->id);
        Bus::assertDispatched(FetchParliamentMemberDetail::class, fn ($job): bool => $job->memberId === $missing->id);
        Bus::assertDispatched(FetchParliamentMemberDetail::class, fn ($job): bool => $job->memberId === $failed->id);
    }
}