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

	async visitHomepage() {
		await this.page.goto( '/' );
	}

	async visitDashboard() {
		await this.admin.visitAdminPage( 'index.php', '' );
	}
}

export default TestPage;
