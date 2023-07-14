import { Admin, RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import type { Page } from '@playwright/test';

class TestPage {
	page: Page;
	admin: Admin;
	requestUtils: RequestUtils;

	constructor( {
		page,
		admin,
		requestUtils,
	}: {
		page: Page;
		admin: Admin;
		requestUtils: RequestUtils;
	} ) {
		this.page = page;
		this.admin = admin;
		this.requestUtils = requestUtils;
	}

	async setLocale( locale: string ) {
		await this.admin.visitAdminPage( 'options-general.php', '' );

		// en_US has an empty value in the language dropdown.
		await this.page
			.locator( 'id=WPLANG' )
			.selectOption( locale === 'en_US' ? '' : locale );

		await this.page.locator( 'id=submit' ).click();
	}

	async clearCaches() {
		await this.page.request.head(
			`${ this.requestUtils.baseURL }/?clear-cache=opcache`
		);
		await this.page.request.head(
			`${ this.requestUtils.baseURL }/?clear-cache=object-cache`
		);
		await this.page.request.head(
			`${ this.requestUtils.baseURL }/?clear-cache=apcu`
		);
	}

	async visitHomepage() {
		await this.page.goto( '/' );
	}

	async visitDashboard() {
		await this.admin.visitAdminPage( 'index.php', '' );
	}
}

export default TestPage;
