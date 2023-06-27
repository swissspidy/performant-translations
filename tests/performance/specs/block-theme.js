import {
	activateTheme,
	activatePlugin,
	deactivatePlugin,
	createURL,
} from '@wordpress/e2e-test-utils';
import { writeFileSync } from 'node:fs';
import { getResultsFilename, median } from '../utils';
import { setLocale } from '../../e2e-utils';

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
		[ 'en', false ],
		[ 'de_DE', false ],
		[ 'de_DE', true ],
	] )( 'Locale: %s, Ginger-MO: %s', ( locale, installPlugin ) => {
		beforeAll( async () => {
			await setLocale( locale );

			if ( installPlugin ) {
				await activatePlugin( 'ginger-mo' );
			}
		} );

		afterAll( async () => {
			await setLocale( 'en' );
			await deactivatePlugin( 'ginger-mo' );
		} );

		it( 'Server Timing Metrics', async () => {
			const result = {};

			let i = TEST_RUNS;

			while ( i-- ) {
				await page.goto( createURL( '/' ) );

				const serverTiming = await page.evaluate( () =>
					performance
						.getEntriesByType( 'navigation' )[ 0 ]
						.serverTiming.reduce( ( acc, entry ) => {
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
				'Ginger-MO': installPlugin,
				...Object.fromEntries(
					Object.entries( result ).map( ( [ key, value ] ) => [
						key,
						median( value ),
					] )
				),
			} );
		} );
	} );
} );
