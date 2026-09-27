<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic accounts for the Playwright e2e suite.
 *
 * Emails are encrypted at rest, so accounts are matched on email_hash
 * (the column the login flow actually looks up).
 */
class E2ESeeder extends Seeder
{
    use WithoutModelEvents;

    public const PASSWORD = 'e2e-password-123';

    public const ACCOUNTS = [
        'user' => ['name' => 'E2E Voter', 'username' => 'e2e_voter', 'email' => 'e2e-user@example.test', 'role' => Role::USER],
        'admin' => ['name' => 'E2E Admin', 'username' => 'e2e_admin', 'email' => 'e2e-admin@example.test', 'role' => Role::ADMIN],
        'superadmin' => ['name' => 'E2E Super Admin', 'username' => 'e2e_superadmin', 'email' => 'e2e-superadmin@example.test', 'role' => Role::SUPERADMIN],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            $this->upsertAccount($account);
        }

        $this->command?->info('Seeded '.count(self::ACCOUNTS).' e2e accounts (password: '.self::PASSWORD.')');
    }

    private function upsertAccount(array $account): void
    {
        User::updateOrCreate(
            ['email_hash' => hash('sha256', $account['email'])],
            [
                'name' => $account['name'],
                'username' => $account['username'],
                'email' => $account['email'],
                'password' => Hash::make(self::PASSWORD),
                'role_id' => Role::idFor($account['role']),
                'email_verified_at' => now(),
            ]
        );
    }
}
