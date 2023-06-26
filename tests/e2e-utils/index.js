import { visitAdminPage } from '@wordpress/e2e-test-utils';

export async function setLocale( locale ) {
	await visitAdminPage( 'options-general.php' );

	const localesDropdown = await page.$( '#WPLANG' );
	await localesDropdown.select( locale );

	await Promise.all( [
		page.click( '#submit' ),
		page.waitForNavigation( {
			waitUntil: 'networkidle0',
		} ),
	] );
}
