<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Communications -> Create Message entry in the sidebar points at
 * messages.create. That route used to render a view that did not exist, so
 * clicking it produced "View [messages.create] not found" in production.
 */
class MessageComposeTest extends TestCase
{
    use RefreshDatabase;

    private ?User $superAdmin = null;

    private function superAdmin(): User
    {
        return $this->superAdmin ??= User::factory()->create([
            'name' => 'Root Operator',
            'username' => 'root-operator',
            'role' => 'admin',
            'role_id' => Role::idFor(Role::SUPERADMIN),
        ]);
    }

    /**
     * @param  list<string>  $permissionNames
     */
    private function staffWith(array $permissionNames, string $roleName = 'message_clerk'): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => 'Message Clerk',
                'description' => 'Posts public constituency messages',
                'grants_admin_access' => true,
                'has_full_access' => false,
                'is_system' => false,
                'sort_order' => 62,
            ]
        );

        $role->permissions()->sync(
            Permission::query()->whereIn('name', $permissionNames)->pluck('id')->all()
        );

        return User::factory()->create([
            'name' => 'Clerk '.$roleName,
            'username' => 'clerk-'.$roleName,
            'role' => 'admin',
            'role_id' => $role->id,
        ]);
    }

    public function test_the_create_message_page_exists_for_staff_who_may_send(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('messages.create'))
            ->assertOk()
            ->assertSee('Create Message')
            ->assertSee('Mombasa')
            ->assertSee('name="county"', false)
            ->assertSee('name="constituency"', false)
            ->assertSee('name="message"', false);

        $this->actingAs($this->staffWith(['messages.create', 'messages.view'], 'composer'))
            ->get(route('messages.create'))
            ->assertOk();
    }

    public function test_staff_without_the_send_permission_cannot_open_the_composer(): void
    {
        $this->actingAs($this->staffWith(['messages.view'], 'reader'))
            ->get(route('messages.create'))
            ->assertForbidden();
    }

    public function test_a_web_message_is_stored_and_the_sender_is_sent_back_to_the_list(): void
    {
        $composer = $this->staffWith(['messages.create', 'messages.view'], 'composer-two');

        $this->actingAs($composer)
            ->post(route('messages.store'), [
                'message' => 'Meet us at the market on Saturday.',
                'county' => 'Nairobi',
                'constituency' => 'Kibra',
            ])
            ->assertRedirect(route('dashboard.messages'))
            ->assertSessionHas('success');

        $message = Message::query()->where('message', 'Meet us at the market on Saturday.')->first();

        $this->assertNotNull($message);
        $this->assertSame($composer->username, $message->username);
        $this->assertSame('Nairobi', $message->county);
        $this->assertSame('Kibra', $message->constituency);
    }

    public function test_the_composer_requires_a_county_a_constituency_and_a_message(): void
    {
        $this->actingAs($this->staffWith(['messages.create', 'messages.view'], 'composer-three'))
            ->post(route('messages.store'), [])
            ->assertSessionHasErrors(['county', 'constituency', 'message']);

        $this->assertSame(0, Message::query()->count());
    }

    public function test_the_messages_list_links_to_the_composer_only_when_it_is_usable(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('dashboard.messages'))
            ->assertOk()
            ->assertSee(route('messages.create'), false);

        $this->actingAs($this->staffWith(['messages.view'], 'reader-two'))
            ->get(route('dashboard.messages'))
            ->assertOk()
            ->assertDontSee(route('messages.create'), false);
    }
}
