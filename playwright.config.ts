import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.WP_BASE_URL ?? 'http://localhost:8888';

process.env.WP_BASE_URL = baseURL;
process.env.STORAGE_STATE_PATH ??= `${ __dirname }/tests/e2e/.auth/admin.json`;

export default defineConfig( {
	testDir: './tests/e2e/specs',
	outputDir: './tests/e2e/.results',
	globalSetup: require.resolve( './tests/e2e/globalSetup.ts' ),
	fullyParallel: false,
	workers: 1,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [
				[ 'github' ],
				[
					'html',
					{ open: 'never', outputFolder: 'tests/e2e/.report' },
				],
		  ]
		: 'list',
	timeout: 60_000,
	expect: { timeout: 15_000 },
	use: {
		baseURL,
		storageState: process.env.STORAGE_STATE_PATH,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [ { name: 'chromium', use: { ...devices[ 'Desktop Chrome' ] } } ],
} );
