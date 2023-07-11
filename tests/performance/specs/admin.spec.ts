import { test } from '../fixtures';
import { testCases, median } from '../utils';
import { Scenario } from '../utils/types';

test.describe( 'Server Timing - WordPress Admin', () => {
	for ( const testCase of testCases ) {
		const { locale, scenario, objectCache } = testCase;

		test.describe( `Locale: ${ locale }, Scenario: ${ scenario }, Object Cache: ${
			objectCache ? 'Yes' : 'No'
		}`, () => {
			test.beforeAll( async ( { requestUtils } ) => {
				if ( objectCache ) {
					await requestUtils.activatePlugin( 'sq-lite-object-cache' );
				}

				if ( scenario === Scenario.Dynamo ) {
					await requestUtils.activatePlugin( 'dyna-mo' );
				}

				if ( scenario === Scenario.NativeGettext ) {
					await requestUtils.activatePlugin( 'native-gettext' );
				}

				if ( scenario === Scenario.ObjectCache ) {
					await requestUtils.activatePlugin( 'wp-performance-pack' );
				}

				if ( scenario === Scenario.Apcu ) {
					await requestUtils.activatePlugin( 'translations-cache' );
				}

				if (
						scenario === Scenario.GingerMo ||
						scenario === Scenario.GingerMoPhp
				) {
					await requestUtils.activatePlugin( 'ginger-mo' );
				}

				if ( scenario === Scenario.GingerMo ) {
					await requestUtils.activatePlugin( 'ginger-mo-no-php' );
				}
			} );

			test.afterAll( async ( { requestUtils } ) => {
				await requestUtils.deactivatePlugin( 'dyna-mo' );
				await requestUtils.deactivatePlugin( 'ginger-mo' );
				await requestUtils.deactivatePlugin( 'ginger-mo-no-php' );
				await requestUtils.deactivatePlugin( 'sq-lite-object-cache' );
				await requestUtils.deactivatePlugin( 'native-gettext' );
				await requestUtils.deactivatePlugin( 'wp-performance-pack' );
				await requestUtils.deactivatePlugin( 'translations-cache' );
			} );

			test( 'Server Timing Metrics', async ( {
				admin,
				settingsPage,
				wpPerformancePack,
				metrics,
			}, testInfo ) => {
				await settingsPage.setLocale( locale );

				if ( scenario === Scenario.ObjectCache ) {
					await wpPerformancePack.enableL10n();
				}

				const result: Record< string, number[] > = {};

				let i = Number( process.env.TEST_RUNS );

				while ( i-- ) {
					await admin.visitAdminPage( 'index.php', '' );

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
					await admin.visitAdminPage( 'index.php', '' );

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

				await settingsPage.setLocale( '' );

				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );
			} );
		} );
	}
} );
