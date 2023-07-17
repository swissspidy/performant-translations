import { Admin, RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import type { Page } from '@playwright/test';

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

export default WpPerformancePack;
