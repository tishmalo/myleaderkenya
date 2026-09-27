import { accounts, expect, signIn, test } from './support/test.js'

test.describe('regular user', () => {
    test('is sent to complete their profile after signing in', async ({ page }) => {
        await signIn(page, 'user')

        await expect(page.getByRole('heading', { name: /profile/i }).first()).toBeVisible()
    })
})

test.describe('admin', () => {
    test('lands on the admin dashboard after signing in', async ({ page }) => {
        await signIn(page, 'admin')

        await expect(
            page.getByRole('navigation').getByRole('link', { name: /dashboard/i }).first(),
        ).toBeVisible()
    })
})

test.describe('sign in errors', () => {
    test('rejects a wrong password and stays on the login page', async ({ page }) => {
        await page.goto('/login')
        await page.getByLabel('Email Address').fill(accounts.user.email)
        await page.getByLabel('Password', { exact: true }).fill('definitely-not-the-password')
        await page.getByRole('button', { name: /sign in/i }).click()

        await expect(page.locator('p.text-red-400').first()).toBeVisible()
        await expect(page).toHaveURL(/\/login$/)
    })

    test('redirects guests away from the admin dashboard', async ({ page }) => {
        await page.goto('/dashboard')

        await expect(page).toHaveURL(/\/login$/)
    })
})
