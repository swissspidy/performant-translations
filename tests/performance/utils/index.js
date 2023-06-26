const tablemark = require( 'tablemark' );

/**
 * Computes the median number from an array numbers.
 *
 * @param {number[]} array
 *
 * @return {number} Median.
 */
function median( array ) {
	const mid = Math.floor( array.length / 2 );
	const numbers = [ ...array ].sort( ( a, b ) => a - b );
	const result =
		array.length % 2 !== 0
			? numbers[ mid ]
			: ( numbers[ mid - 1 ] + numbers[ mid ] ) / 2;

	return Number( result.toFixed( 2 ) );
}

/**
 * Gets the result file name.
 *
 * @param {string} fileName File name.
 *
 * @return {string} Result file name.
 */
function getResultsFilename( fileName ) {
	return `${ fileName.replace( '.js', '' ) }.results.json`;
}

/**
 * Format test results as a Markdown table.
 *
 * @param {Array} results Test results.
 *
 * @return {string} Markdown content.
 */
function formatAsMarkdownTable( results ) {
	if ( ! results?.length ) {
		return '';
	}

	function toCellText( v ) {
		if ( v === true || v === 'true' ) return '✅';
		if ( ! v  || v === 'false' ) return '';
		return v?.toString() || String( v );
	}

	return tablemark( results, {
		// In v2 the option is still called stringify
		stringify: toCellText,
		caseHeaders: false,
		columns: [
			{ align: 'left' },
			{ align: 'center' },
			{ align: 'center' },
			{ align: 'center' },
			{ align: 'center' },
		],
	} );
}

module.exports = {
	median,
	getResultsFilename,
	formatAsMarkdownTable,
};
