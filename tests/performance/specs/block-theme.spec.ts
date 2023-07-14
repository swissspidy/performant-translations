import { test } from '../fixtures';
import { testCases, iterate } from '../utils';
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
			test.beforeAll( async ( { testUtils } ) => {
				await testUtils.prepareTestCase( { scenario, objectCache } );
			} );

			test.afterAll( async ( { testUtils } ) => {
				await testUtils.teardown();
			} );

			test( 'Server Timing Metrics', async ( {
				testPage,
				wpPerformancePack,
				metrics,
			}, testInfo ) => {
				await testPage.clearCaches();

				await testPage.setLocale( locale );

				if ( scenario === Scenario.ObjectCache ) {
					await wpPerformancePack.enableL10n();
				}

				const results = {
					Locale: locale,
					Scenario: scenario,
					'Object Cache': objectCache,
					...( await iterate( async () => ( {
						...( await metrics.getServerTiming( [
							'wp-memory-usage',
							'wp-total',
						] ) ),
						TTFB: await metrics.getTimeToFirstByte(),
					} ) ) ),
					...( await iterate(
						async () => ( {
							...( await metrics.getLighthouseReport() ),
						} ),
						Number( process.env.LIGHTHOUSE_RUNS )
					) ),
				};

				await testPage.setLocale( 'en_US' );

				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );
			} );
		} );
	}
} );
