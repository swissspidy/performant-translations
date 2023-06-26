import { visitAdminPage, getOption } from '@wordpress/e2e-test-utils';

import { setLocale } from '../../e2e-utils';

describe( 'Translation Loading', () => {
	beforeAll( async () => {
		await setLocale( 'de_DE' );
	} );

	afterAll( async () => {
		await setLocale( 'en' );
	} );

	it( 'should correctly translate strings', async () => {
		// Just to ensure the setup in beforeAll() has worked.
		const installedLocales = await getOption( 'WPLANG' );
		await expect( installedLocales ).toStrictEqual( 'de_DE' );

		await visitAdminPage( 'index.php' );

		const defaultOutput = await page.$eval(
			'#dashboard_site_health .postbox-header h2',
			( el: HTMLElement ) => el.innerText
		);
		expect( defaultOutput ).toContain( 'Zustand der Website' );
	} );
} );
