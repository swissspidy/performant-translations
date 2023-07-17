import { test } from '../fixtures';
import { testCases, iterate } from '../utils';

test.describe( 'WordPress Admin', () => {
	for ( const testCase of testCases ) {
		const { locale, scenario } = testCase;

		test.describe( `Locale: ${ locale }, Scenario: ${ scenario }`, () => {
			test.beforeAll( async ( { testUtils } ) => {
				await testUtils.prepareTestCase( testCase );
			} );

			test.afterAll( async ( { testUtils } ) => {
				await testUtils.resetSite();
			} );

			test( 'Collect Metrics', async ( {
				testPage,
				metrics,
			}, testInfo ) => {
				const results = {
					Locale: locale,
					Scenario: scenario,
					...( await iterate( async () => {
						await testPage.visitDashboard();
						return {
							...( await metrics.getServerTiming( [
								'wp-memory-usage',
								'wp-total',
								'wp-locale-switching',
							] ) ),
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
