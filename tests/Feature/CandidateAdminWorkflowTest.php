<?php

namespace Tests\Feature;

use App\Jobs\ExportCandidates;
use App\Jobs\ImportCandidates;
use App\Models\Candidate;
use App\Models\CandidateTransferRun;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Notifications\CandidateClaimLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The admin side of aspirant management: listing, search, claim links and the
 * background CSV import/export runs.
 */
class CandidateAdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function candidate(array $attributes = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => 'Jane Doe',
            'position_id' => Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor'])->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'approval_status' => 'approved',
        ], $attributes));
    }

    public function test_the_listing_respects_the_name_filter(): void
    {
        $this->candidate(['name' => 'Vincent Simiyu Wanjal']);
        $this->candidate(['name' => 'Mercy Wanjiku']);

        $this->actingAs($this->admin())
            ->get(route('candidates.index', ['candidate' => 'Vincent']))
            ->assertOk()
            ->assertSee('Vincent Simiyu Wanjal')
            ->assertDontSee('Mercy Wanjiku');
    }

    public function test_the_search_endpoint_matches_names_and_nicknames(): void
    {
        $this->candidate(['name' => 'Jane Doe', 'nick_name' => 'JD']);

        $this->actingAs($this->admin())
            ->getJson(route('candidates.search', ['q' => 'JD']))
            ->assertOk()
            ->assertJsonPath('results.0.id', Candidate::query()->value('id'))
            ->assertJsonPath('results.0.nickname', 'JD')
            ->assertJsonPath('results.0.text', 'Jane Doe (JD)');
    }

    public function test_the_search_endpoint_returns_everything_for_an_empty_term(): void
    {
        $this->candidate(['name' => 'Jane Doe']);
        $this->candidate(['name' => 'Mercy Wanjiku']);

        $this->actingAs($this->admin())
            ->getJson(route('candidates.search'))
            ->assertOk()
            ->assertJsonCount(2, 'results');
    }

    public function test_the_featured_toggle_updates_the_profile(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($this->admin())
            ->patchJson(route('candidates.featured', $candidate), ['featured' => true])
            ->assertOk()
            ->assertJson(['success' => true, 'featured' => true]);

        $this->assertTrue($candidate->fresh()->featured);
    }

    public function test_the_featured_toggle_requires_a_boolean(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($this->admin())
            ->patchJson(route('candidates.featured', $candidate), ['featured' => 'maybe'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('featured');
    }

    public function test_a_claim_link_is_stored_and_mailed(): void
    {
        Notification::fake();

        $candidate = $this->candidate(['email' => 'jane@example.com']);

        $this->actingAs($this->admin())
            ->post(route('candidates.claim-link', $candidate))
            ->assertRedirect()
            ->assertSessionHas('success', 'Claim link queued for jane@example.com.');

        $candidate->refresh();

        $this->assertNotNull($candidate->claim_token_hash);
        $this->assertNotNull($candidate->claim_token_expires_at);
        $this->assertNotNull($candidate->claim_sent_at);

        Notification::assertSentOnDemand(CandidateClaimLinkNotification::class);
    }

    public function test_a_claim_link_needs_an_email_address(): void
    {
        Notification::fake();

        $candidate = $this->candidate(['email' => null]);

        $this->actingAs($this->admin())
            ->post(route('candidates.claim-link', $candidate))
            ->assertRedirect()
            ->assertSessionHas('warning', 'Add an email address before sending a claim link.');

        $this->assertNull($candidate->fresh()->claim_sent_at);
        Notification::assertNothingSent();
    }

    public function test_a_claimed_profile_cannot_be_sent_another_claim_link(): void
    {
        Notification::fake();

        $candidate = $this->candidate(['email' => 'jane@example.com']);
        $candidate->forceFill(['claimed_at' => now()])->save();

        $this->actingAs($this->admin())
            ->post(route('candidates.claim-link', $candidate))
            ->assertRedirect()
            ->assertSessionHas('warning', 'This aspirant account has already been claimed.');

        $this->assertNull($candidate->fresh()->claim_sent_at);
        Notification::assertNothingSent();
    }

    public function test_an_import_is_queued_for_the_user(): void
    {
        Queue::fake();
        Storage::fake('local');

        $this->actingAs($this->admin())
            ->post(route('candidates.import'), [
                'file' => UploadedFile::fake()->create('candidates.csv', 4, 'text/csv'),
            ])
            ->assertRedirect(route('candidates.index'));

        $run = CandidateTransferRun::query()->sole();

        $this->assertSame('import', $run->type);
        $this->assertSame('pending', $run->status);
        $this->assertSame($this->admin()->id, $run->requested_by);
        $this->assertNotNull($run->source_path);

        Queue::assertPushed(ImportCandidates::class);
    }

    public function test_an_import_requires_a_csv_file(): void
    {
        Queue::fake();

        $this->actingAs($this->admin())
            ->post(route('candidates.import'), [
                'file' => UploadedFile::fake()->image('portrait.jpg'),
            ])
            ->assertSessionHasErrors('file');

        Queue::assertNothingPushed();
    }

    public function test_an_export_is_queued_with_the_current_filters(): void
    {
        Queue::fake();

        $this->actingAs($this->admin())
            ->post(route('candidates.export'), [
                'candidate' => 'Jane',
                'position' => '3',
                'approval_status' => 'approved',
            ])
            ->assertRedirect();

        $run = CandidateTransferRun::query()->sole();

        $this->assertSame('export', $run->type);
        $this->assertSame([
            'candidate' => 'Jane',
            'position' => '3',
            'approval_status' => 'approved',
        ], $run->filters);

        Queue::assertPushed(ExportCandidates::class);
    }

    public function test_the_transfer_status_reports_progress_and_the_download_link(): void
    {
        $run = CandidateTransferRun::create([
            'type' => 'export',
            'status' => 'complete',
            'result_path' => 'exports/candidates.csv',
            'exported_count' => 12,
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('candidates.transfer.status', $run))
            ->assertOk()
            ->assertJson([
                'id' => $run->id,
                'type' => 'export',
                'status' => 'complete',
                'exported_count' => 12,
                'download_url' => route('candidates.export.download', $run),
            ]);
    }

    public function test_a_run_that_is_not_finished_has_no_download(): void
    {
        $run = CandidateTransferRun::create([
            'type' => 'export',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('candidates.transfer.status', $run))
            ->assertOk()
            ->assertJsonPath('download_url', null);

        $this->actingAs($this->admin())
            ->get(route('candidates.export.download', $run))
            ->assertNotFound();
    }

    public function test_a_finished_export_downloads_the_csv(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/candidates.csv', "name\nJane Doe\n");

        $run = CandidateTransferRun::create([
            'type' => 'export',
            'status' => 'complete',
            'result_path' => 'exports/candidates.csv',
            'download_name' => 'aspirants.csv',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('candidates.export.download', $run))
            ->assertOk();

        $this->assertStringContainsString('aspirants.csv', (string) $response->headers->get('content-disposition'));
    }

    public function test_the_import_template_streams_the_expected_columns(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('candidates.import.template'))
            ->assertOk();

        $this->assertStringContainsString(
            'candidates_import_template.csv',
            (string) $response->headers->get('content-disposition')
        );

        $content = $response->streamedContent();

        $this->assertStringContainsString('name,nick_name,phone,email,political_party', $content);
        $this->assertStringContainsString('"Jane Doe",JD,0712345678,jane@example.com,UDA', $content);
    }

    public function test_a_pending_import_can_be_published(): void
    {
        $candidate = $this->candidate([
            'approval_status' => 'pending',
            'is_imported' => true,
            'import_status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->post(route('candidates.import.publish', $candidate))
            ->assertRedirect()
            ->assertSessionHas('success', 'Jane Doe published.');

        $candidate->refresh();

        $this->assertSame('published', $candidate->import_status);
        $this->assertSame('approved', $candidate->approval_status);
    }

    public function test_a_published_registration_is_not_treated_as_an_import(): void
    {
        $candidate = $this->candidate(['approval_status' => 'pending']);

        $this->actingAs($this->admin())
            ->post(route('candidates.import.publish', $candidate))
            ->assertRedirect()
            ->assertSessionHas('warning', 'Only pending imported aspirants can be published.');

        $this->assertSame('pending', $candidate->fresh()->approval_status);
    }

    public function test_an_import_can_be_discarded(): void
    {
        $candidate = $this->candidate([
            'approval_status' => 'pending',
            'is_imported' => true,
            'import_status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->post(route('candidates.import.discard', $candidate))
            ->assertRedirect()
            ->assertSessionHas('success', 'Jane Doe discarded.');

        $candidate->refresh();

        $this->assertSame('discarded', $candidate->import_status);
        $this->assertSame('rejected', $candidate->approval_status);
    }

    public function test_pending_aspirants_stay_out_of_the_public_directory(): void
    {
        $candidate = $this->candidate(['approval_status' => 'pending']);

        $this->get(route('aspirants.show', $candidate))->assertNotFound();
    }

    public function test_the_public_profile_redirects_to_the_canonical_slug(): void
    {
        $candidate = $this->candidate(['name' => 'Jane Doe']);
        $candidate->forceFill(['slug' => 'jane-doe'])->save();

        $this->get('/aspirants/'.$candidate->id)
            ->assertStatus(301)
            ->assertRedirect(route('aspirants.show', $candidate));

        $this->get(route('aspirants.show', $candidate))->assertOk();
    }
}
