<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\County;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bulk imports must never outrank real aspirants on the public listings: the
 * registered profiles come first and imported ones follow, however recently the
 * import ran.
 */
class AspirantListingOrderTest extends TestCase
{
    use RefreshDatabase;

    private function position(string $name = 'President'): Position
    {
        return Position::firstOrCreate(['name' => $name], ['description' => $name]);
    }

    private function candidate(string $name, array $attributes = []): Candidate
    {
        $candidate = Candidate::create(array_merge([
            'name' => $name,
            'position_id' => $this->position()->id,
            'country' => 'Kenya',
            'approval_status' => 'approved',
        ], array_diff_key($attributes, ['created_at' => true])));

        if (isset($attributes['created_at'])) {
            $candidate->forceFill(['created_at' => $attributes['created_at']])->saveQuietly();
        }

        return $candidate;
    }

    private function imported(string $name, string $createdAt): Candidate
    {
        return $this->candidate($name, [
            'is_imported' => true,
            'import_status' => 'published',
            'created_at' => $createdAt,
        ]);
    }

    public function test_imported_aspirants_come_after_registered_ones_on_the_aspirants_page(): void
    {
        // An imported profile newer than a registered one, to prove recency no
        // longer decides the order.
        $this->candidate('Registered Mwangi', ['created_at' => now()->subDays(3)]);
        $this->candidate('Registered Achieng', ['created_at' => now()->subDays(1)]);
        $this->imported('Imported Kamau', now()->subDays(2)->toDateTimeString());
        $this->imported('Imported Wanjiku', now()->toDateTimeString());

        $this->get(route('aspirants.public', ['position' => $this->position()->id]))
            ->assertOk()
            ->assertSeeInOrder([
                'Registered Achieng',
                'Registered Mwangi',
                'Imported Wanjiku',
                'Imported Kamau',
            ]);
    }

    public function test_imported_aspirants_come_last_within_position_groups(): void
    {
        $this->candidate('Registered Otieno', ['created_at' => now()->subDays(2)]);
        $this->imported('Imported Nyambura', now()->toDateTimeString());

        $this->get(route('aspirants.public'))
            ->assertOk()
            ->assertSeeInOrder(['Registered Otieno', 'Imported Nyambura']);
    }

    public function test_imported_aspirants_come_last_on_a_county_page(): void
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Central'], ['tribes' => []]);
        $county = County::create(['name' => 'Nairobi', 'bloc_id' => $bloc->id]);
        $position = $this->position('Governor');

        $this->candidate('Registered Karanja', [
            'position_id' => $position->id,
            'county' => 'Nairobi',
        ]);
        $this->candidate('Imported Auma', [
            'position_id' => $position->id,
            'county' => 'Nairobi',
            'is_imported' => true,
            'import_status' => 'published',
        ]);

        $this->get(route('county.show', $county))
            ->assertOk()
            ->assertSeeInOrder(['Registered Karanja', 'Imported Auma']);
    }

    public function test_a_pending_import_stays_off_the_public_listing(): void
    {
        $this->candidate('Registered Wekesa');
        $this->candidate('Pending Import', [
            'is_imported' => true,
            'import_status' => 'pending',
            'approval_status' => 'pending',
        ]);

        $this->get(route('aspirants.public', ['position' => $this->position()->id]))
            ->assertOk()
            ->assertSee('Registered Wekesa')
            ->assertDontSee('Pending Import');
    }
}
