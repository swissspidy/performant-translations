import { Scenario } from './types';

export const testCases = [
	{ locale: 'en_US', scenario: Scenario.Default, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.Default, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.GingerMo, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.GingerMoPhp, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.NativeGettext, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.Dynamo, objectCache: false },
	{ locale: 'de_DE', scenario: Scenario.Apcu, objectCache: false },
	{ locale: 'en_US', scenario: Scenario.Default, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.Default, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.GingerMo, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.GingerMoPhp, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.NativeGettext, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.Dynamo, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.Apcu, objectCache: true },
	{ locale: 'de_DE', scenario: Scenario.ObjectCache, objectCache: true },
];

/**
 * Computes the median number from an array numbers.
 *
 * @param array List of numbers.
 * @return Median.
 */
export function median( array: number[] ) {
	const mid = Math.floor( array.length / 2 );
	const numbers = [ ...array ].sort( ( a, b ) => a - b );
	const result =
		array.length % 2 !== 0
			? numbers[ mid ]
			: ( numbers[ mid - 1 ] + numbers[ mid ] ) / 2;

	return Number( result.toFixed( 2 ) );
}
