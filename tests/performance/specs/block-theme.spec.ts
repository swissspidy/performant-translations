import { test } from '../fixtures';
import { testCases, median } from '../utils';
import { Scenario } from '../utils/types';

test.describe( 'Server Timing - Twenty Twenty-Three', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await requestUtils.activateTheme( 'twentytwentythree' );
	} );

	for ( const testCase of testCases ) {
		const { locale, scenario, objectCache } = testCase;

		test.describe( `Locale: ${ locale }, Scenario: ${ scenario }, Object Cache: ${
			objectCache ? 'Yes' : 'No'
		}`, () => {
			test.beforeAll( async ( { requestUtils } ) => {
				await Promise.all( [
					objectCache &&
						requestUtils.activatePlugin( 'sq-lite-object-cache' ),
					scenario === Scenario.NativeGettext &&
						requestUtils.activatePlugin( 'native-gettext' ),
					scenario === Scenario.CacheL10n &&
						requestUtils.activatePlugin( 'wp-performance-pack' ),
					( scenario === Scenario.GingerMo ||
						scenario === Scenario.GingerMoPhp ) &&
						requestUtils.activatePlugin( 'ginger-mo' ),
					scenario === Scenario.GingerMo &&
						requestUtils.activatePlugin( 'ginger-mo-no-php' ),
				] );
			} );

			test.afterAll( async ( { requestUtils } ) => {
				await Promise.all( [
					requestUtils.deactivatePlugin( 'ginger-mo' ),
					requestUtils.deactivatePlugin( 'ginger-mo-no-php' ),
					requestUtils.deactivatePlugin( 'sq-lite-object-cache' ),
					requestUtils.deactivatePlugin( 'native-gettext' ),
					requestUtils.deactivatePlugin( 'wp-performance-pack' ),
				] );
			} );

			test( 'Server Timing Metrics', async ( {
				page,
				settingsPage,
				wpPerformancePack,
				metrics,
			}, testInfo ) => {
				await settingsPage.setLocale( locale );

				if ( scenario === Scenario.CacheL10n ) {
					await wpPerformancePack.enableL10n();
				}

				const result: Record< string, number[] > = {};

				let i = Number( process.env.TEST_RUNS );

				while ( i-- ) {
					await page.goto( '/' );

					const allMetrics = {
						...( await metrics.getServerTiming() ),
					};

					for ( const [ key, value ] of Object.entries(
						allMetrics
					) ) {
						result[ key ] ??= [];
						result[ key ].push( value );
					}
				}

				i = Number( process.env.LIGHTHOUSE_RUNS );

				while ( i-- ) {
					await page.goto( '/' );

					const allMetrics = {
						...( await metrics.getLighthouseReport() ),
					};

					for ( const [ key, value ] of Object.entries(
						allMetrics
					) ) {
						result[ key ] ??= [];
						result[ key ].push( value );
					}
				}

				const results = {
					Locale: locale,
					Scenario: scenario,
					'Object Cache': objectCache,
					...Object.fromEntries(
						Object.entries( result ).map( ( [ key, value ] ) => [
							key,
							median( value as number[] ),
						] )
					),
				};

				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );

				await settingsPage.setLocale( '' );
			} );
		} );
	}
} );
