import { expect, test } from './support/test.js'

/**
 * The boot loader covers the viewport at z-index 100000 until the layout script
 * removes it, so every spec waits for it before interacting.
 */
async function gotoLanding(page) {
    await page.goto('/')
    await expect(page.locator('#siteBootLoader')).toHaveCount(0, { timeout: 15_000 })
}

test.describe('public landing page', () => {
    test('renders the hero and core sections without console errors', async ({ page, baseURL }) => {
        const consoleErrors = []
        const failedFirstPartyRequests = []

        page.on('console', (message) => {
            if (message.type() === 'error') {
                consoleErrors.push(message.text())
            }
        })
        page.on('requestfailed', (request) => {
            if (request.url().startsWith(baseURL)) {
                failedFirstPartyRequests.push(`${request.method()} ${request.url()}`)
            }
        })

        const response = await page.goto('/')

        expect(response?.status()).toBe(200)
        await expect(page).toHaveTitle(/Kenya Aspirants and Candidates for 2027 General Election/i)

        await expect(page.getByRole('heading', { level: 1 })).toContainText('2027 Kenya')
        await expect(page.getByRole('link', { name: /download app/i })).toBeVisible()
        await expect(page.getByRole('link', { name: /campaign tools/i }).first()).toBeVisible()

        // Only sections that render regardless of database content.
        for (const heading of [
            'Aspirants From President to MCA',
            'Live Registration Statistics',
            /How to Get Your Voter.s Card/,
            'About My Leader Kenya Tuko Kadi Program',
        ]) {
            await expect(page.getByRole('heading', { name: heading })).toBeVisible()
        }

        expect(failedFirstPartyRequests, 'no first-party requests should fail').toEqual([])
        expect(consoleErrors, 'no console errors should be logged').toEqual([])
    })

    test('featured aspirants carousel fetches its endpoint', async ({ page }) => {
        const aspirantRequest = page.waitForResponse(
            (response) => response.url().includes('/featured-aspirants') && response.status() === 200,
        )

        await page.goto('/')

        const payload = await (await aspirantRequest).json()

        expect(Array.isArray(payload.data)).toBe(true)
        await expect(page.locator('[data-aspirant-carousel]')).toBeVisible()
    })

    test('opens the auth modal in register mode and can switch to login', async ({ page }) => {
        await gotoLanding(page)

        // The backdrop is toggled with the `open` class (opacity 0 otherwise),
        // so visibility is not a reliable signal here.
        const modal = page.locator('#authModal')
        await expect(modal).not.toHaveClass(/\bopen\b/)

        // This call to action opens the register panel, not the login one.
        await page.getByRole('button', { name: /join the movement/i }).click()

        await expect(modal).toHaveClass(/\bopen\b/)

        const registerPanel = modal.locator('#panel-register')
        await expect(registerPanel).toBeVisible()
        await expect(registerPanel.locator('input[name="email"]')).toBeVisible()

        await modal.getByRole('button', { name: 'Login', exact: true }).click()

        const loginPanel = modal.locator('#panel-login')
        await expect(loginPanel).toBeVisible()
        await expect(loginPanel.locator('input[name="email"]')).toBeVisible()
        await expect(loginPanel.locator('input[name="password"]')).toBeVisible()
    })

    test('footer privacy link navigates to the privacy page', async ({ page }) => {
        await gotoLanding(page)

        await page.getByRole('link', { name: 'Privacy Policy' }).click()

        await expect(page).toHaveURL(/\/privacy$/)
    })
})
