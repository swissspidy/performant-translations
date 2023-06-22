import {
	visitAdminPage,
	setOption,
	getOption,
} from '@wordpress/e2e-test-utils';

describe( 'Translation Loading', () => {
	beforeAll( async () => {
		await visitAdminPage( 'options-general.php' );

		const localesDropdown = await page.$( '#WPLANG' );
		await localesDropdown.select( 'de_DE' );

		await Promise.all( [
			page.click( '#submit' ),
			page.waitForNavigation( {
				waitUntil: 'networkidle0',
			} ),
		] );
	} );

	afterAll( async () => {
		// Extra space just so page.type() types something to clear the input field.
		await setOption( 'WPLANG', ' ' );
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
