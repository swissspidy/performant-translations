import {
	activateTheme,
	activatePlugin,
	deactivatePlugin,
	createURL,
} from '@wordpress/e2e-test-utils';
import { writeFileSync } from 'node:fs';
import { getResultsFilename, median } from '../utils';
import {
	setLocale,
	enablePerformancePackL10n,
	getServerTiming,
} from '../../e2e-utils';

enum Scenario {
	Default = 'Default',
	GingerMo = 'Ginger-MO (MO)',
	GingerMoPhp = 'Ginger-MO (PHP)',
	CacheL10n = 'Cache translations',
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
		{ locale: 'en', scenario: Scenario.Default, objectCache: false },
		{ locale: 'de_DE', scenario: Scenario.Default, objectCache: false },
		{ locale: 'de_DE', scenario: Scenario.GingerMo, objectCache: false },
		{ locale: 'de_DE', scenario: Scenario.GingerMoPhp, objectCache: false },
		{
			locale: 'de_DE',
			scenario: Scenario.NativeGettext,
			objectCache: false,
		},
		{ locale: 'en', scenario: Scenario.Default, objectCache: true },
		{ locale: 'de_DE', scenario: Scenario.Default, objectCache: true },
		{ locale: 'de_DE', scenario: Scenario.CacheL10n, objectCache: true },
		{ locale: 'de_DE', scenario: Scenario.GingerMo, objectCache: true },
		{ locale: 'de_DE', scenario: Scenario.GingerMoPhp, objectCache: true },
		{
			locale: 'de_DE',
			scenario: Scenario.NativeGettext,
			objectCache: true,
		},
	] )(
		'Locale: $locale, Scenario: $scenario',
		( { locale, scenario, objectCache } ) => {
			beforeAll( async () => {
				await setLocale( locale );

				if ( objectCache ) {
					await activatePlugin( 'sqlite-object-cache' );
				}

				if ( scenario === Scenario.NativeGettext ) {
					await activatePlugin( 'native-gettext' );
				}

				if ( scenario === Scenario.CacheL10n ) {
					await activatePlugin( 'wp-performance-pack' );
					await enablePerformancePackL10n();
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

					const serverTiming = await getServerTiming();

					for ( const [ key, value ] of Object.entries(
						serverTiming
					) ) {
						if ( ! result[ key ] ) {
							result[ key ] = [];
						}
						result[ key ].push( value );
					}
				}

				results.push( {
					Locale: locale,
					Scenario: scenario,
					'Object Cache': objectCache,
					...Object.fromEntries(
						Object.entries( result ).map( ( [ key, value ] ) => [
							key,
							median( value as number[] ),
						] )
					),
				} );
			} );
		}
	);
} );
