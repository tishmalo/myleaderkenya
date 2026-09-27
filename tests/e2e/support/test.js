import { expect, test } from '@playwright/test'

import { accounts, landingPaths } from './accounts.js'

/**
 * Signs in through the real login form and asserts the account landed on the
 * dashboard its role is routed to (see DashboardDestinationService::urlFor).
 *
 * Use it at the start of a test rather than a fixture so the auth path stays
 * visible in the test body.
 */
export async function signIn(page, role = 'user') {
    const account = accounts[role]

    if (!account) {
        throw new Error(`Unknown e2e role "${role}".`)
    }

    await page.goto('/login')
    await page.getByLabel('Email Address').fill(account.email)
    await page.getByLabel('Password', { exact: true }).fill(account.password)
    await page.getByRole('button', { name: /sign in/i }).click()

    await expect(page).toHaveURL(new RegExp(`${landingPaths[role].replace(/[/.]/g, '\\$&')}$`))

    return page
}

export { expect, test, accounts, landingPaths }
