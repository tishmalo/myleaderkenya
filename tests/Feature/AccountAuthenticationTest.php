<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_account_registration_does_not_require_position(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'General User',
            'phone' => '+254700000100',
            'email' => 'general@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

        $response->assertRedirect(route('my-account', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email_hash' => hash('sha256', 'general@example.test'),
        ]);
    }

    public function test_login_finds_user_by_encrypted_email_hash(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.test',
            'password' => 'secure-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'LOGIN@example.test',
            'password' => 'secure-password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}