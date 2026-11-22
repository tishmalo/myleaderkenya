<?php

namespace Tests\Feature;

use App\Models\CampaignTool;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SeoPage;
use App\Models\User;
use Database\Seeders\SeoPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPageTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE_NAME = 'campaign-tools.public';

    private function superAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    private function page(array $overrides = []): SeoPage
    {
        return SeoPage::create(array_merge([
            'route_name' => self::ROUTE_NAME,
            'title' => 'Campaign Tools',
            'description' => 'Campaign tools for Kenya 2027 aspirants.',
            'canonical' => null,
        ], $overrides));
    }

    public function test_the_seeder_ships_the_previously_hardcoded_metadata(): void
    {
        $this->seed(SeoPageSeeder::class);

        $this->assertDatabaseHas('seo_pages', [
            'route_name' => self::ROUTE_NAME,
            'title' => 'Kenya Election Campaign Software Tools | My Leader Kenya',
            'description' => 'Powerful campaign tools for Kenya 2027 aspirants: bulk SMS, opinion polls, call center, website builder, support groups and more. Available on My Leader Kenya.',
            'canonical' => null,
        ]);
    }

    public function test_the_public_page_renders_the_stored_metadata(): void
    {
        $this->seed(SeoPageSeeder::class);

        $response = $this->get(route(self::ROUTE_NAME))->assertOk();

        $response->assertSee('<title>Kenya Election Campaign Software Tools | My Leader Kenya</title>', false);
        $response->assertSee('Powerful campaign tools for Kenya 2027 aspirants', false);
        $response->assertSee('<link rel="canonical" href="'.route(self::ROUTE_NAME).'">', false);
    }

    public function test_admin_changes_are_saved_and_rendered_on_the_public_page(): void
    {
        $this->seed(SeoPageSeeder::class);
        $page = SeoPage::query()->where('route_name', self::ROUTE_NAME)->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->put(route('seo-pages.update', $page), [
                'title' => 'Campaign Software for Kenya 2027 | My Leader Kenya',
                'description' => 'Bulk SMS, opinion polls and call center software for Kenyan campaigns.',
                'canonical' => 'https://myleader.co.ke/campaign-tools',
            ])
            ->assertRedirect(route('seo-pages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('seo_pages', [
            'id' => $page->id,
            'title' => 'Campaign Software for Kenya 2027 | My Leader Kenya',
            'canonical' => 'https://myleader.co.ke/campaign-tools',
        ]);

        $response = $this->get(route(self::ROUTE_NAME))->assertOk();

        $response->assertSee('<title>Campaign Software for Kenya 2027 | My Leader Kenya</title>', false);
        $response->assertSee('Bulk SMS, opinion polls and call center software for Kenyan campaigns.', false);
        $response->assertSee('https://myleader.co.ke/campaign-tools', false);
    }

    public function test_a_blank_canonical_falls_back_to_the_page_url(): void
    {
        $page = $this->page();

        $this->actingAs($this->superAdmin())
            ->put(route('seo-pages.update', $page), [
                'title' => 'Campaign Tools',
                'description' => 'Campaign tools for Kenya 2027 aspirants.',
                'canonical' => '',
            ])
            ->assertRedirect(route('seo-pages.index'));

        $this->assertNull($page->fresh()->canonical);

        $this->get(route(self::ROUTE_NAME))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route(self::ROUTE_NAME).'">', false);
    }

    public function test_invalid_metadata_is_rejected(): void
    {
        $page = $this->page();

        $this->actingAs($this->superAdmin())
            ->put(route('seo-pages.update', $page), [
                'title' => '',
                'description' => str_repeat('a', 161),
                'canonical' => 'not-a-url',
            ])
            ->assertSessionHasErrors(['title', 'description', 'canonical']);

        $this->assertSame('Campaign Tools', $page->fresh()->title);
        $this->assertNull($page->fresh()->canonical);
    }

    public function test_managing_seo_pages_requires_the_frontend_pages_permission(): void
    {
        $page = $this->page();

        $clerk = User::factory()->create([
            'name' => 'Clerk Kim',
            'username' => 'clerk-kim',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::ADMIN),
        ]);

        Role::query()->where('name', Role::ADMIN)->firstOrFail()
            ->permissions()
            ->detach(
                Permission::query()
                    ->whereIn('name', ['frontend-pages.view', 'frontend-pages.update'])
                    ->pluck('id')
            );

        $this->actingAs($clerk)->get(route('seo-pages.index'))->assertForbidden();
        $this->actingAs($clerk->fresh())->get(route('seo-pages.edit', $page))->assertForbidden();
        $this->actingAs($clerk->fresh())
            ->put(route('seo-pages.update', $page), [
                'title' => 'Hijacked title',
                'description' => 'Hijacked description.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('seo_pages', ['title' => 'Hijacked title']);
    }

    public function test_pages_without_stored_metadata_keep_the_service_defaults(): void
    {
        $response = $this->get(route(self::ROUTE_NAME))->assertOk();

        $response->assertSee('My Leader Kenya - Niko Kadi', false);
    }

    public function test_the_seeded_tool_page_uses_its_own_metadata(): void
    {
        CampaignTool::create([
            'title' => 'Bulk SMS',
            'slug' => 'bulk-sms',
            'excerpt' => 'Send bulk SMS to supporters.',
            'content' => 'Bulk SMS tool content.',
            'status' => 'published',
            'sort_order' => 1,
        ]);

        $response = $this->get(route('campaign-tools.show', 'bulk-sms'))->assertOk();

        $response->assertSee('Send bulk SMS to supporters.', false);
        $response->assertSee('<link rel="canonical" href="'.route('campaign-tools.show', 'bulk-sms').'">', false);
    }
}
