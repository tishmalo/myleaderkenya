<?php

namespace Tests\Feature;

use App\Models\Bloc;
use App\Models\Candidate;
use App\Models\CandidateTransferRun;
use App\Models\County;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The candidates management page, including the import/export job panel that
 * sits under the table.
 */
class CandidatesIndexTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function seedCandidate(): Candidate
    {
        $bloc = Bloc::firstOrCreate(['name' => 'Western'], ['tribes' => []]);
        County::create(['name' => 'Bungoma', 'bloc_id' => $bloc->id]);

        return Candidate::create([
            'name' => 'Vincent Simiyu Wanjal',
            'position_id' => Position::firstOrCreate(['name' => 'Governor'], ['description' => 'Governor'])->id,
            'country' => 'Kenya',
            'county' => 'Bungoma',
            'approval_status' => 'approved',
        ]);
    }

    public function test_the_page_lists_candidates(): void
    {
        $this->seedCandidate();

        $this->actingAs($this->superAdmin())
            ->get(route('candidates.index'))
            ->assertOk()
            ->assertSee('Candidates Management')
            ->assertSee('Vincent Simiyu Wanjal')
            ->assertSee('Bungoma');
    }

    public function test_the_job_panel_sits_under_the_table_and_explains_itself(): void
    {
        $this->seedCandidate();

        CandidateTransferRun::create([
            'type' => 'import',
            'status' => 'complete',
            'imported_count' => 3,
            'linked_count' => 1,
            'skipped_count' => 2,
            'filters' => [],
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('candidates.index'))
            ->assertOk()
            ->assertSee('Import / Export Jobs')
            ->assertSee('3 imported, 1 linked, 2 skipped')
            ->assertSee('The CSV imports and exports started from the buttons at the top of this page.');

        $html = (string) $response->getContent();
        $table = strpos($html, 'Vincent Simiyu Wanjal');
        $panel = strpos($html, 'data-transfer-panel');

        $this->assertNotFalse($table);
        $this->assertNotFalse($panel);
        $this->assertGreaterThan($table, $panel, 'The job panel should follow the candidates table.');
    }

    public function test_the_panel_is_hidden_when_no_jobs_ran(): void
    {
        $this->seedCandidate();

        $this->actingAs($this->superAdmin())
            ->get(route('candidates.index'))
            ->assertOk()
            ->assertDontSee('Import / Export Jobs')
            ->assertDontSee('data-transfer-panel', false);
    }
}
