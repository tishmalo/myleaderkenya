import { execFileSync } from 'node:child_process'
import path from 'node:path'

const EXPECTED_DATABASE = process.env.E2E_DATABASE ?? 'myleader_e2e'

const PROBE = `
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$connection = config('database.default');
echo config('database.connections.'.$connection.'.database');
`

function probe() {
    return execFileSync('php', ['-r', PROBE], {
        cwd: path.resolve(import.meta.dirname, '../../..'),
        env: { ...process.env, APP_ENV: 'testing' },
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'pipe'],
    }).trim()
}

/**
 * Fails the run before any browser starts if the app is not pointed at the
 * dedicated e2e database.
 */
export default function globalSetup() {
    let database

    try {
        database = probe()
    } catch (error) {
        throw new Error(
            `Could not determine the e2e database. Is .env.testing present and migrated?\n${error.stderr ?? error.message}`,
        )
    }

    if (database !== EXPECTED_DATABASE) {
        throw new Error(
            `Refusing to run e2e tests: expected database "${EXPECTED_DATABASE}" but the app is using "${database}".`,
        )
    }

    console.log(`e2e database confirmed: ${database}`)
}
