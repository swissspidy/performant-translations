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

export async function enablePerformancePackL10n() {
	await visitAdminPage( 'options-general.php', 'page=wppp_options_page' );

	const l10nCheckbox = await page.$( '#mod_l10n_improvements_id' );
	await l10nCheckbox.click();

	await Promise.all( [
		page.click( '#submit' ),
		page.waitForNavigation( {
			waitUntil: 'networkidle0',
		} ),
	] );

	await visitAdminPage(
		'options-general.php',
		'page=wppp_options_page&tab=l10n_improvements'
	);

	await page.click( '#use_mo_dynamic_id' );
	await page.click( '#mo-caching' );

	await Promise.all( [
		page.click( '#submit' ),
		page.waitForNavigation( {
			waitUntil: 'networkidle0',
		} ),
	] );
}

export async function getServerTiming() {
	return page.evaluate< () => Record< string, number > >( () =>
		(
			performance.getEntriesByType(
				'navigation'
			) as PerformanceNavigationTiming[]
		 )[ 0 ].serverTiming.reduce( ( acc, entry ) => {
			acc[ entry.name ] = entry.duration;
			return acc;
		}, {} )
	);
}
