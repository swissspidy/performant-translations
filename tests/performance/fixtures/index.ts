import {
	test as base,
	Admin,
	RequestUtils,
} from '@wordpress/e2e-test-utils-playwright';
import type { Page } from '@playwright/test';
import { chromium, type Browser } from 'playwright';
import lighthouse from 'lighthouse';
import getPort from 'get-port';

class SettingsPage {
	admin: Admin;
	page: Page;

	constructor( { admin, page }: { admin: Admin; page: Page } ) {
		this.admin = admin;
		this.page = page;
	}

	async setLocale( locale: string ) {
		await this.admin.visitAdminPage( 'options-general.php', '' );

		// en_US has an empty value in the language dropdown.
		await this.page
			.locator( 'id=WPLANG' )
			.selectOption( locale === 'en_US' ? '' : locale );

		await this.page.locator( 'id=submit' ).click();
	}
}

class WpPerformancePack {
	admin: Admin;
	page: Page;
	requestUtils: RequestUtils;

	constructor( {
		admin,
		page,
		requestUtils,
	}: {
		admin: Admin;
		page: Page;
		requestUtils: RequestUtils;
	} ) {
		this.admin = admin;
		this.page = page;
		this.requestUtils = requestUtils;
	}

	async enableL10n() {
		// Try to activate the plugin (again) just in case.
		// Reduces test flakiness in case it didn't work previously.
		await this.requestUtils.activatePlugin( 'wp-performance-pack' );

		await this.admin.visitAdminPage(
			'options-general.php',
			'page=wppp_options_page'
		);

		await this.page.locator( 'id=mod_l10n_improvements_id' ).click();

		await this.page.locator( 'id=submit' ).click();

		await this.admin.visitAdminPage(
			'options-general.php',
			'page=wppp_options_page&tab=l10n_improvements'
		);

		await this.page.locator( 'id=use_mo_dynamic_id' ).click();
		await this.page.locator( 'id=mo-caching' ).click();

		await this.page.locator( 'id=submit' ).click();
	}
}

class Metrics {
	constructor( public readonly page: Page, public readonly port: number ) {
		this.page = page;
		this.port = port;
	}

	/**
	 * Returns durations from the Server-Timing header.
	 *
	 * @param fields Optional fields to filter.
	 */
	async getServerTiming( fields: string[] = [] ) {
		return this.page.evaluate< Record< string, number >, string[] >(
			( fields: string[] ) =>
				(
					performance.getEntriesByType(
						'navigation'
					) as PerformanceNavigationTiming[]
				 )[ 0 ].serverTiming.reduce< Record< string, number > >(
					( acc, entry ) => {
						if (
							fields.length === 0 ||
							fields.includes( entry.name )
						) {
							acc[ entry.name ] = entry.duration;
						}
						return acc;
					},
					{}
				),
			fields
		);
	}

	/**
	 * Returns time to first byte (TTFB) from PerformanceObserver.
	 */
	async getTimeToFirstByte() {
		return this.page.evaluate< number >(
			() =>
				(
					performance.getEntriesByType(
						'navigation'
					) as PerformanceNavigationTiming[]
				 )[ 0 ].responseStart
		);
	}

	async getLighthouseReport() {
		const result = await lighthouse(
			this.page.url(),
			{ port: this.port },
			undefined
		);

		if ( ! result ) {
			return {} as Record< string, number >;
		}

		const { lhr } = result;

		const LCP = lhr.audits[ 'largest-contentful-paint' ].numericValue || 0;
		const TBT = lhr.audits[ 'total-blocking-time' ].numericValue || 0;
		const TTI = lhr.audits.interactive.numericValue || 0;
		const CLS = lhr.audits[ 'cumulative-layout-shift' ].numericValue || 0;

		return {
			LCP,
			TBT,
			TTI,
			CLS,
		};
	}
}

type PerformanceFixtures = {
	settingsPage: SettingsPage;
	metrics: Metrics;
	wpPerformancePack: WpPerformancePack;
};

export const test = base.extend<
	PerformanceFixtures,
	{
		requestUtils: RequestUtils;
		port: number;
		browser: Browser;
	}
>( {
	// Override requestUtils from @wordpress/e2e-test-utils-playwright
	// to avoid trashing all posts initially and looking for GB-specific plugins.
	// @ts-ignore -- TODO: Fix types.
	requestUtils: [
		async ( {}, use, workerInfo ) => {
			const requestUtils = await RequestUtils.setup( {
				baseURL: workerInfo.project.use.baseURL,
				storageStatePath: process.env.STORAGE_STATE_PATH,
			} );

			await use( requestUtils );
		},
		{ scope: 'worker', auto: true },
	],
	settingsPage: async ( { admin, page }, use ) => {
		await use( new SettingsPage( { admin, page } ) );
	},
	wpPerformancePack: async ( { admin, page, requestUtils }, use ) => {
		await use( new WpPerformancePack( { admin, page, requestUtils } ) );
	},
	port: [
		async ( {}, use ) => {
			const port = await getPort();
			await use( port );
		},
		{ scope: 'worker' },
	],
	browser: [
		async ( { port }, use ) => {
			const browser = await chromium.launch( {
				args: [ `--remote-debugging-port=${ port }` ],
			} );
			await use( browser );

			await browser.close();
		},
		{ scope: 'worker' },
	],
	metrics: async ( { page, port }, use ) => {
		await use( new Metrics( page, port ) );
	},
} );

export { expect } from '@wordpress/e2e-test-utils-playwright';
