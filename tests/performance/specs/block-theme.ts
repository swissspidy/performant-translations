import {
	activateTheme,
	activatePlugin,
	deactivatePlugin,
	createURL,
	setOption,
} from '@wordpress/e2e-test-utils';
import { writeFileSync } from 'node:fs';
import { getResultsFilename, median } from '../utils';
import { setLocale } from '../../e2e-utils';

enum Scenario {
	Default = 'Default',
	GingerMo = 'Ginger-MO (MO)',
	GingerMoPhp = 'Ginger-MO (PHP)',
	Sqlite = 'SQLite Object Cache',
	NativeGettext = 'Native Gettext',
}

describe( 'Server Timing - Twenty Twenty-Three', () => {
	const results = [];

	beforeAll( async () => {
		await activateTheme( 'twentytwentythree' );
	} );

	afterAll( async () => {
		const resultsFilename = getResultsFilename( __filename );
		writeFileSync( resultsFilename, JSON.stringify( results, null, 2 ) );
	} );

	describe.each( [
		{ locale: 'en', scenario: Scenario.Default },
		{ locale: 'en', scenario: Scenario.Sqlite },
		{ locale: 'de_DE', scenario: Scenario.Default },
		{ locale: 'de_DE', scenario: Scenario.GingerMo },
		{ locale: 'de_DE', scenario: Scenario.GingerMoPhp },
		{ locale: 'de_DE', scenario: Scenario.Sqlite },
		{ locale: 'de_DE', scenario: Scenario.NativeGettext },
	] )( 'Locale: $locale, Scenario: $scenario', ( { locale, scenario } ) => {
		beforeAll( async () => {
			await setLocale( locale );

			if ( scenario === Scenario.NativeGettext ) {
				await activatePlugin( 'native-gettext' );
			}

			if ( scenario === Scenario.Sqlite ) {
				await activatePlugin( 'sqlite-object-cache' );

				if ( locale !== 'en' ) {
					await activatePlugin( 'wp-performance-pack' );
					// Enable l10n object caching in WP Performance Pack but nothing else.
					await setOption(
						'wppp_option',
						'a:3:{s:21:"mod_l10n_improvements";b:1;s:14:"use_mo_dynamic";b:0;s:10:"mo_caching";b:1;}'
					);
				}
			}

			if (
				scenario === Scenario.GingerMo ||
				scenario === Scenario.GingerMoPhp
			) {
				await activatePlugin( 'ginger-mo' );
			}

			if ( scenario === Scenario.GingerMo ) {
				await activatePlugin( 'ginger-mo-no-php' );
			}
		} );

		afterAll( async () => {
			await setLocale( 'en' );
			await deactivatePlugin( 'ginger-mo' );
			await deactivatePlugin( 'ginger-mo-no-php' );
			await deactivatePlugin( 'sqlite-object-cache' );
			await deactivatePlugin( 'native-gettext' );
			await deactivatePlugin( 'wp-performance-pack' );
		} );

		it( 'Server Timing Metrics', async () => {
			const result: Record< string, number[] > = {};

			let i = globalThis.TEST_RUNS;

			while ( i-- ) {
				await page.goto( createURL( '/' ) );

				const serverTiming: Record< string, number > =
					await page.evaluate( () =>
						(
							performance.getEntriesByType(
								'navigation'
							) as PerformanceNavigationTiming[]
						 )[ 0 ].serverTiming.reduce( ( acc, entry ) => {
							acc[ entry.name ] = entry.duration;
							return acc;
						}, {} )
					);

				for ( const [ key, value ] of Object.entries( serverTiming ) ) {
					if ( ! result[ key ] ) {
						result[ key ] = [];
					}
					result[ key ].push( value );
				}
			}

			results.push( {
				Locale: locale,
				Scenario: scenario,
				...Object.fromEntries(
					Object.entries( result ).map( ( [ key, value ] ) => [
						key,
						median( value as number[] ),
					] )
				),
			} );
		} );
	} );
} );
