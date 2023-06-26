/**
 * External dependencies.
 */
const { basename, join } = require( 'node:path' );
const { writeFileSync } = require( 'node:fs' );

/**
 * WordPress dependencies.
 */
import {
	activateTheme,
	activatePlugin,
	deactivatePlugin,
	createURL,
	setOption,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
const { getResultsFilename } = require( './../utils' );

describe( 'Server Timing - Twenty Twenty Three', () => {
	const results = {
		wpBeforeTemplate: [],
		wpTemplate: [],
		wpTotal: [],
	};

	beforeAll( async () => {
		await activateTheme( 'twentytwentythree' );
	} );

	afterAll( async () => {
		const resultsFilename = getResultsFilename(
			basename( __filename, '.js' )
		);
		writeFileSync(
			join( __dirname, resultsFilename ),
			JSON.stringify( results, null, 2 )
		);
	} );

	describe.each( [
		[ 'en_US', false ],
		[ 'de_DE', false ],
		[ 'de_DE', true ],
	] )( 'Locale: %s, Ginger-MO: %s', ( locale, installPlugin ) => {
		beforeAll( async () => {
			await setOption( 'WPLANG', locale );

			if ( installPlugin ) {
				await activatePlugin( 'ginger-mo' );
			}
		} );

		afterAll( async () => {
			// Extra space just so page.type() types something to clear the input field.
			await setOption( 'WPLANG', ' ' );
			await deactivatePlugin( 'ginger-mo' );
		} );

		it( 'Server Timing Metrics', async () => {
			let i = TEST_RUNS;
			while ( i-- ) {
				await page.goto( createURL( '/' ) );
				const navigationTimingJson = await page.evaluate( () =>
					JSON.stringify(
						performance.getEntriesByType( 'navigation' )
					)
				);

				const [ navigationTiming ] = JSON.parse( navigationTimingJson );

				results.wpBeforeTemplate.push(
					navigationTiming.serverTiming[ 0 ].duration
				);
				results.wpTemplate.push(
					navigationTiming.serverTiming[ 1 ].duration
				);
				results.wpTotal.push(
					navigationTiming.serverTiming[ 2 ].duration
				);
			}
		} );
	} );
} );
