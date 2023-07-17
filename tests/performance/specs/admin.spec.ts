import { test } from '../fixtures';
import { testCases, iterate } from '../utils';

test.describe( 'Server Timing - WordPress Admin', () => {
	for ( const testCase of testCases ) {
		const { locale, scenario, localeSwitching } = testCase;

		test.describe( `Locale: ${ locale }, Scenario: ${ scenario }, Object Cache: ${
			localeSwitching ? 'Yes' : 'No'
		}`, () => {
			test.beforeAll( async ( { testUtils } ) => {
				await testUtils.prepareTestCase( testCase );
			} );

			test.afterAll( async ( { testUtils } ) => {
				await testUtils.resetSite();
			} );

			test( 'Server Timing Metrics', async ( {
				testPage,
				metrics,
			}, testInfo ) => {
				const results = {
					Locale: locale,
					Scenario: scenario,
					'Locale Switching': localeSwitching,
					...( await iterate( async () => {
						await testPage.visitDashboard(
							localeSwitching ? 'switch-locales=1' : ''
						);
						return {
							...( await metrics.getServerTiming( [
								'wp-memory-usage',
								'wp-total',
								'wp-locale-switching',
							] ) ),
							TTFB: await metrics.getTimeToFirstByte(),
						};
					} ) ),
					...( await iterate( async () => {
						await testPage.visitDashboard();
						return {
							...( await metrics.getLighthouseReport() ),
						};
					}, Number( process.env.LIGHTHOUSE_RUNS ) ) ),
				};

				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );
			} );
		} );
	}
} );
