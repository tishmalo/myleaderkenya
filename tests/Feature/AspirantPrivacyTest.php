<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\CandidateClaimRequest;
use App\Models\Position;
use App\Models\User;
use App\Services\Web\CandidateClaimRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AspirantPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_search_returns_only_allowlisted_non_pii_fields(): void
    {
        $position = Position::create(['name' => 'Governor', 'sort_order' => 1]);
        $candidate = Candidate::create([
            'name' => 'Privacy Safe Aspirant',
            'email' => 'private@example.test',
            'phone' => '+254700000001',
            'position_id' => $position->id,
            'approval_status' => 'approved',
            'county' => 'Nairobi',
        ]);

        $response = $this->getJson(route('aspirants.search', ['q' => 'Privacy']));

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('results.0.id', $candidate->id)
            ->assertJsonMissing(['email' => 'private@example.test'])
            ->assertJsonMissing(['phone' => '+254700000001']);

        $this->assertSame(
            ['id', 'name', 'nickname', 'image_url', 'position', 'party', 'jurisdiction'],
            array_keys($response->json('results.0'))
        );
    }

    public function test_claim_registration_preselects_only_public_candidate_data(): void
    {
        $position = Position::create(['name' => 'Governor', 'sort_order' => 1]);
        $candidate = Candidate::create([
            'name' => 'Locked Public Aspirant',
            'email' => 'locked-private@example.test',
            'phone' => '+254700000010',
            'position_id' => $position->id,
            'approval_status' => 'approved',
            'county' => 'Nakuru',
        ]);

        $response = $this->get(route('aspirants.register', ['candidate_id' => $candidate->id, 'modal' => 1]));

        $response->assertOk()
            ->assertSee('Claim this aspirant profile')
            ->assertSee('data-aspirant-search-locked', false)
            ->assertSee('value="'.$candidate->id.'"', false)
            ->assertSee('Locked Public Aspirant')
            ->assertDontSee('locked-private@example.test')
            ->assertDontSee('+254700000010')
            ->assertDontSee('Choose a different aspirant');
    }

    public function test_unapproved_candidate_cannot_be_preselected_for_a_claim(): void
    {
        $position = Position::create(['name' => 'Senator', 'sort_order' => 1]);
        $candidate = Candidate::create([
            'name' => 'Pending Private Aspirant',
            'position_id' => $position->id,
            'approval_status' => 'pending',
        ]);

        $this->get(route('aspirants.register', ['candidate_id' => $candidate->id]))
            ->assertNotFound();
    }
    public function test_candidate_user_and_claim_request_store_pii_as_ciphertext(): void
    {
        $position = Position::create(['name' => 'Governor', 'sort_order' => 1]);
        $candidate = Candidate::create([
            'name' => 'Encrypted Candidate',
            'email' => 'candidate@example.test',
            'phone' => '+254700000002',
            'position_id' => $position->id,
            'approval_status' => 'pending',
        ]);
        $user = User::factory()->create([
            'email' => 'claimant@example.test',
            'phone' => '+254700000003',
        ]);
        $claim = CandidateClaimRequest::create([
            'candidate_id' => $candidate->id,
            'relationship' => 'PA',
            'name' => 'Claimant',
            'email' => 'request@example.test',
            'phone' => '+254700000004',
            'password' => 'secure-password',
            'status' => 'pending',
        ]);

        $rawCandidate = DB::table('candidates')->find($candidate->id);
        $rawUser = DB::table('users')->find($user->id);
        $rawClaim = DB::table('candidate_claim_requests')->find($claim->id);

        $this->assertNotSame('candidate@example.test', $rawCandidate->email);
        $this->assertNotSame('+254700000002', $rawCandidate->phone);
        $this->assertNotSame('claimant@example.test', $rawUser->email);
        $this->assertNotSame('+254700000003', $rawUser->phone);
        $this->assertNotSame('request@example.test', $rawClaim->email);
        $this->assertNotSame('+254700000004', $rawClaim->phone);
        $this->assertSame(hash('sha256', 'claimant@example.test'), $rawUser->email_hash);
        $this->assertSame(hash('sha256', 'request@example.test'), $rawClaim->email_hash);
    }

    public function test_existing_candidate_submission_creates_only_a_claim_request(): void
    {
        $position = Position::create(['name' => 'Governor', 'sort_order' => 1]);
        $candidate = Candidate::create([
            'name' => 'Existing Aspirant',
            'email' => 'existing-private@example.test',
            'phone' => '+254700000007',
            'position_id' => $position->id,
            'approval_status' => 'approved',
        ]);
        $claims = $this->mock(CandidateClaimRequestService::class);
        $claims->shouldReceive('createPublicRequest')->once()->withArgs(
            fn (Candidate $selected, array $data): bool =>
                $selected->is($candidate)
                && $data['relationship'] === 'PA'
                && $data['name'] === 'Submitting PA'
                && $data['email'] === 'pa@example.test'
                && $data['phone'] === '+254700000008'
        );

        $response = $this->post(route('aspirants.register.store'), [
            'candidate_id' => $candidate->id,
            'submission_mode' => 'representative',
            'relationship' => 'PA',
            'account_name' => 'Submitting PA',
            'account_email' => 'pa@example.test',
            'account_phone' => '+254700000008',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'aspirant_name' => 'Tampered Name',
            'aspirant_email' => 'attacker@example.test',
            'aspirant_phone' => '+254700000009',
        ]);

        $response->assertRedirect(route('aspirants.register', ['candidate_id' => $candidate->id]));
        $this->assertDatabaseCount('candidates', 1);
        $this->assertSame('Existing Aspirant', $candidate->fresh()->name);
        $this->assertSame('existing-private@example.test', $candidate->fresh()->email);
        $this->assertSame('+254700000007', $candidate->fresh()->phone);
    }
}
