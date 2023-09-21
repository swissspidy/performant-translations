/**
 * External dependencies
 */
import { join } from 'node:path';
import { defineConfig, devices } from '@playwright/test';

const STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	join(process.cwd(), 'artifacts/storage-states/admin.json');

const config = defineConfig({
	reporter: process.env.CI ? [['github'], ['list']] : 'list',
	forbidOnly: !!process.env.CI,
	workers: 1,
	retries: process.env.CI ? 2 : 0,
	timeout: parseInt(process.env.TIMEOUT || '', 10) || 100_000, // Defaults to 100 seconds.
	// Don't report slow test "files", as we will be running our tests in serial.
	reportSlowTests: null,
	testDir: 'specs',
	outputDir: join(process.cwd(), 'artifacts/test-results'),
	snapshotPathTemplate:
		'{testDir}/{testFileDir}/__snapshots__/{arg}-{projectName}{ext}',
	globalSetup: './config/global-setup.ts',
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
		headless: true,
		viewport: {
			width: 960,
			height: 700,
		},
		ignoreHTTPSErrors: true,
		locale: 'en-US',
		contextOptions: {
			reducedMotion: 'reduce',
			strictSelectors: true,
		},
		storageState: STORAGE_STATE_PATH,
		actionTimeout: 10_000, // 10 seconds.
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'on-first-retry',
	},
	webServer: {
		command: 'npm run wp-env start',
		port: 8889,
		timeout: 120_000, // 120 seconds.
		reuseExistingServer: true,
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
});

export default config;
