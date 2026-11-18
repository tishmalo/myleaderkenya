<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AspirantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_listing_only_returns_approved_aspirants(): void
    {
        $this->candidate('Approved Aspirant', 'approved');
        $this->candidate('Nairobi Aspirant', 'approved', ['county' => 'Nairobi']);
        $this->candidate('Pending Aspirant', 'pending');

        $this->getJson('/api/aspirants')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Approved Aspirant'])
            ->assertJsonMissing(['name' => 'Pending Aspirant']);
    }

    public function test_the_listing_honours_location_filters_and_page_size(): void
    {
        $this->candidate('Nairobi Aspirant', 'approved', ['county' => 'Nairobi']);
        $this->candidate('Mombasa Aspirant', 'approved', ['county' => 'Mombasa']);

        $this->getJson('/api/aspirants?county=Nairobi&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.county', 'Nairobi')
            ->assertJsonPath('per_page', 1);
    }

    public function test_a_pending_aspirant_is_not_publicly_visible(): void
    {
        $candidate = $this->candidate('Pending Aspirant', 'pending');

        $this->getJson('/api/aspirants/' . $candidate->id)->assertNotFound();
    }

    public function test_an_approved_aspirant_includes_related_news(): void
    {
        $candidate = $this->candidate('Approved Aspirant', 'approved');
        $user = User::factory()->create();

        $article = NewsArticle::create([
            'title' => 'Aspirant launches manifesto',
            'slug' => 'aspirant-launches-manifesto',
            'excerpt' => 'A summary of the launch.',
            'content' => 'Full story.',
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $article->candidates()->attach($candidate->id);

        $this->getJson('/api/aspirants/' . $candidate->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Approved Aspirant')
            ->assertJsonPath('data.related_news.0.slug', 'aspirant-launches-manifesto')
            ->assertJsonPath('data.related_news.0.tags', []);
    }

    public function test_the_profile_endpoint_returns_the_signed_in_aspirant(): void
    {
        $user = $this->aspirantUser();
        $candidate = $this->candidate('Linked Aspirant', 'pending', ['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/aspirant/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $candidate->id)
            ->assertJsonPath('data.about', 'Serving the people.');
    }

    public function test_the_profile_endpoint_returns_not_found_without_a_linked_candidate(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/aspirant/profile')
            ->assertNotFound()
            ->assertJsonPath('message', 'No aspirant profile is linked to this account yet.');
    }

    public function test_an_aspirant_can_register_through_the_api(): void
    {
        $position = Position::create(['name' => 'Governor', 'description' => 'Governor']);

        $response = $this->postJson('/api/aspirants/register', [
            'name' => 'Jane Wanjiru',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'position_id' => $position->id,
            'county' => 'Nairobi',
            'constituency' => 'Westlands',
            'ward' => 'Kitisuru',
            'about' => 'Serving the people.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.username', 'jane_wanjiru')
            ->assertJsonPath('data.name', 'Jane Wanjiru')
            ->assertJsonPath('data.position.name', 'Governor');

        $this->assertNotEmpty($response->json('token'));

        $user = User::where('username', 'jane_wanjiru')->firstOrFail();

        $this->assertTrue((bool) $user->is_aspirant);
        $this->assertDatabaseHas('candidates', [
            'user_id' => $user->id,
            'name' => 'Jane Wanjiru',
            'approval_status' => 'pending',
            'county' => 'Nairobi',
        ]);
        $this->assertDatabaseHas('candidate_user_relationships', [
            'user_id' => $user->id,
            'candidate_id' => Candidate::firstOrFail()->id,
            'relationship' => 'aspirant',
        ]);
    }

    public function test_a_pending_aspirant_can_be_updated_and_returns_to_review(): void
    {
        $candidate = $this->candidate('Pending Aspirant', 'pending');

        $this->putJson('/api/aspirants/' . $candidate->id, [
            'name' => 'Updated Aspirant',
            'about' => 'A fresh agenda.',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Aspirant')
            ->assertJsonPath('data.about', 'A fresh agenda.');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'name' => 'Updated Aspirant',
            'approval_status' => 'pending',
        ]);
    }

    public function test_an_approved_aspirant_cannot_be_updated_through_the_api(): void
    {
        $candidate = $this->candidate('Approved Aspirant', 'approved');

        $this->putJson('/api/aspirants/' . $candidate->id, ['name' => 'Changed'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Approved aspirants cannot be updated through the public API. Ask an admin to make changes.');

        $this->assertDatabaseHas('candidates', ['id' => $candidate->id, 'name' => 'Approved Aspirant']);
    }

    private function aspirantUser(): User
    {
        return User::factory()->create(['is_aspirant' => true, 'role' => 'user']);
    }

    private function candidate(string $name, string $status, array $overrides = []): Candidate
    {
        return Candidate::create(array_merge([
            'name' => $name,
            'position_id' => Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor'])->id,
            'country' => 'Kenya',
            'county' => 'Nairobi',
            'about' => 'Serving the people.',
            'approval_status' => $status,
        ], $overrides));
    }
}
