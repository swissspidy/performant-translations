import { Admin, RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import type { Page } from '@playwright/test';

class TestPage {
	page: Page;
	admin: Admin;
	requestUtils: RequestUtils;
	baseURL?: string;

	constructor( {
		page,
		admin,
		requestUtils,
		baseURL,
	}: {
		page: Page;
		admin: Admin;
		requestUtils: RequestUtils;
		baseURL?: string;
	} ) {
		this.page = page;
		this.admin = admin;
		this.requestUtils = requestUtils;
		this.baseURL = baseURL;
	}

	async visitHomepage( query?: string ) {
		await this.page.goto(
			`${ this.baseURL || '' }/` + ( query ? `?${ query }` : '' )
		);
	}

	async visitDashboard( query: string = '' ) {
		await this.page.goto(
			`${ this.baseURL || '' }/wp-admin/index.php` +
				( query ? `?${ query }` : '' )
		);
	}
}

export default TestPage;
