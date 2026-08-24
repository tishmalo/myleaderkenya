<?php

namespace Tests\Feature;

use App\Models\CampaignPriorityCategory;
use App\Models\Candidate;
use App\Models\CandidateCampaignPriority;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignPriorityFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_only_shows_approved_entries_in_active_categories(): void
    {
        $position = Position::create(['name' => 'Governor']);
        $candidate = Candidate::create(['name' => 'Priority Candidate', 'position_id' => $position->id, 'approval_status' => 'approved']);
        $active = CampaignPriorityCategory::create(['name' => 'Healthcare', 'slug' => 'healthcare', 'icon' => 'fas fa-heart-pulse', 'sort_order' => 1, 'is_active' => true]);
        $pending = CampaignPriorityCategory::create(['name' => 'Roads', 'slug' => 'roads', 'icon' => 'fas fa-road', 'sort_order' => 2, 'is_active' => true]);
        $inactive = CampaignPriorityCategory::create(['name' => 'Retired Group', 'slug' => 'retired-group', 'icon' => 'fas fa-house', 'sort_order' => 3, 'is_active' => false]);
        CandidateCampaignPriority::create(['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $active->id, 'manifesto' => 'Approved healthcare manifesto sentinel.', 'status' => 'approved']);
        CandidateCampaignPriority::create(['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $pending->id, 'manifesto' => 'Pending roads manifesto sentinel.', 'status' => 'pending']);
        CandidateCampaignPriority::create(['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $inactive->id, 'manifesto' => 'Inactive manifesto sentinel.', 'status' => 'approved']);

        $response = $this->get(route('aspirants.show', $candidate));

        $response->assertOk()->assertSee('Approved healthcare manifesto sentinel.')->assertDontSee('Pending roads manifesto sentinel.')->assertDontSee('Inactive manifesto sentinel.');
    }

    public function test_linked_aspirant_can_submit_active_priorities_for_review(): void
    {
        $user = User::factory()->create([
            'is_aspirant' => true,
            'gender' => 'male',
            'year_of_birth' => 1995,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'country_of_residence' => 'Kenya',
        ]);
        $position = Position::create(['name' => 'Senator']);
        $candidate = Candidate::create(['name' => 'Workspace Candidate', 'position_id' => $position->id, 'user_id' => $user->id, 'approval_status' => 'approved']);
        $active = CampaignPriorityCategory::create(['name' => 'Jobs', 'slug' => 'jobs', 'icon' => 'fas fa-briefcase', 'sort_order' => 10, 'is_active' => true]);
        $inactive = CampaignPriorityCategory::create(['name' => 'Hidden', 'slug' => 'hidden', 'icon' => 'fas fa-house', 'sort_order' => 20, 'is_active' => false]);

        $response = $this->actingAs($user)->put(route('aspirant.campaign-priorities.update'), [
            'priorities' => [
                $active->id => ['manifesto' => 'Create sustainable employment opportunities.'],
                $inactive->id => ['manifesto' => 'Tampered inactive category submission.'],
            ],
        ]);

        $response->assertRedirect(route('aspirant.dashboard').'#campaign-priorities');
        $this->assertDatabaseHas('candidate_campaign_priorities', ['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $active->id, 'status' => 'pending', 'manifesto' => 'Create sustainable employment opportunities.']);
        $this->assertDatabaseMissing('candidate_campaign_priorities', ['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $inactive->id]);
    }

    public function test_admin_can_approve_a_priority_for_a_slug_bound_candidate(): void
    {
        $admin = User::factory()->create(['role_id' => Role::idFor(Role::SUPERADMIN), 'role' => 'admin']);
        $position = Position::create(['name' => 'Member of Parliament']);
        $candidate = Candidate::create(['name' => 'Edwin Sifuna', 'position_id' => $position->id, 'approval_status' => 'approved']);
        $category = CampaignPriorityCategory::create(['name' => 'Governance', 'slug' => 'governance', 'icon' => 'fas fa-landmark', 'sort_order' => 1, 'is_active' => true]);
        $priority = CandidateCampaignPriority::create(['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $category->id, 'manifesto' => 'Governance manifesto.', 'status' => 'pending']);

        $response = $this->actingAs($admin)
            ->from(route('campaign-priority-categories.index'))
            ->patch(route('candidate-campaign-priorities.review', [$candidate, $priority]), ['status' => 'approved']);

        $response->assertRedirect(route('campaign-priority-categories.index'));
        $this->assertDatabaseHas('candidate_campaign_priorities', ['id' => $priority->id, 'candidate_id' => $candidate->id, 'status' => 'approved', 'reviewed_by' => $admin->id]);
    }
    public function test_unchanged_approved_manifesto_is_not_returned_to_pending(): void
    {
        $user = User::factory()->create(['is_aspirant' => true]);
        $position = Position::create(['name' => 'President']);
        $candidate = Candidate::create(['name' => 'Approved Priority Candidate', 'position_id' => $position->id, 'user_id' => $user->id, 'approval_status' => 'approved']);
        $category = CampaignPriorityCategory::create(['name' => 'Leadership', 'slug' => 'leadership', 'icon' => 'fas fa-shield-halved', 'sort_order' => 1, 'is_active' => true]);
        CandidateCampaignPriority::create(['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $category->id, 'manifesto' => 'Keep this approved statement.', 'status' => 'approved']);

        $this->actingAs($user)->put(route('aspirant.campaign-priorities.update'), ['priorities' => [$category->id => ['manifesto' => 'Keep this approved statement.']]])->assertRedirect();

        $this->assertDatabaseHas('candidate_campaign_priorities', ['candidate_id' => $candidate->id, 'campaign_priority_category_id' => $category->id, 'status' => 'approved']);
    }
}