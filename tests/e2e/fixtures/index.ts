import {
	test as base,
	Admin,
	RequestUtils,
} from '@wordpress/e2e-test-utils-playwright';
import type { Page } from '@playwright/test';

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

type E2EFixture = {
	settingsPage: SettingsPage;
};

export const test = base.extend< E2EFixture, { requestUtils: RequestUtils } >( {
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
} );

export { expect } from '@wordpress/e2e-test-utils-playwright';
