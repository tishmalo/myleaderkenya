/**
 * Credentials for the accounts created by database/seeders/E2ESeeder.php.
 * Keep in sync with that seeder.
 */
export const E2E_PASSWORD = 'e2e-password-123'

export const accounts = {
    user: { email: 'e2e-user@example.test', password: E2E_PASSWORD },
    admin: { email: 'e2e-admin@example.test', password: E2E_PASSWORD },
    superadmin: { email: 'e2e-superadmin@example.test', password: E2E_PASSWORD },
}

/**
 * Where each account lands after a successful login, per
 * DashboardDestinationService::urlFor().
 */
export const landingPaths = {
    user: '/my-account/profile',
    admin: '/dashboard',
    superadmin: '/dashboard',
}
