const { readFileSync } = require( 'fs' );
const { getResultsFilename } = require( '../utils' );
const { join } = require( 'node:path' );
const { writeFileSync } = require( 'node:fs' );

class PerformanceResultsReporter {
	constructor( globalConfig, reporterOptions, reporterContext ) {
		this._globalConfig = globalConfig;
		this._options = reporterOptions;
		this._context = reporterContext;
	}

	onRunComplete( testContexts, testResults ) {
		const summaryJson = [];

		for ( const testResult of testResults.testResults ) {
			const resultFile = getResultsFilename( testResult.testFilePath );
			const title = testResult.testResults[ 0 ]?.ancestorTitles[ 0 ];
			const results = JSON.parse(
				readFileSync( resultFile, { encoding: 'UTF-8' } )
			);

			console.log( '\n' );
			console.log( 'Results for:', title );
			console.table( results );

			summaryJson.push( {
				file: resultFile,
				title,
				results,
			} );
		}

		writeFileSync(
			join( __dirname, '/../', '/specs/', 'summary.json' ),
			JSON.stringify( summaryJson )
		);
	}
}

module.exports = PerformanceResultsReporter;
