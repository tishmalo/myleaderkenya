import { defineConfig, devices } from '@playwright/test'

const HOST = '127.0.0.1'
const PORT = Number(process.env.E2E_PORT ?? 8321)
const baseURL = `http://${HOST}:${PORT}`

export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/support/global-setup.js',

    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    // The PHP built-in server is single-threaded, so parallel workers only queue
    // requests behind each other and make the suite flaky. Raise E2E_WORKERS
    // only if the server is replaced with a multi-worker one.
    workers: Number(process.env.E2E_WORKERS ?? 1),
    timeout: 60_000,
    expect: { timeout: 10_000 },

    reporter: process.env.CI
        ? [['github'], ['html', { open: 'never' }]]
        : [['list'], ['html', { open: 'never' }]],

    use: {
        baseURL,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'off',
        actionTimeout: 15_000,
    },

    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],

    /**
     * The e2e server is started with `php -S` rather than `artisan serve` on
     * purpose. On this machine `variables_order=GPCS`, so `$_ENV` is empty and
     * `ServeCommand` hands the child process an environment without APP_ENV.
     * The child would then boot on `.env` and hit the production database.
     * Spawning the built-in server directly keeps APP_ENV under our control.
     */
    webServer: {
        command: `php -S ${HOST}:${PORT} -t public server.php`,
        url: baseURL,
        env: { APP_ENV: 'testing' },
        // Fail loudly rather than silently reusing a server bound to real data.
        reuseExistingServer: false,
        timeout: 120_000,
        stdout: 'pipe',
        stderr: 'pipe',
    },
})
