const { readFileSync } = require( 'fs' );
const { getResultsFilename, formatAsMarkdownTable } = require( '../utils' );
const { join } = require( 'node:path' );
const { writeFileSync } = require( 'node:fs' );

class PerformanceResultsReporter {
	constructor( globalConfig, reporterOptions, reporterContext ) {
		this._globalConfig = globalConfig;
		this._options = reporterOptions;
		this._context = reporterContext;
	}

	onRunComplete( testContexts, testResults ) {
		let summary = '';

		for ( const testResult of testResults.testResults ) {
			const resultFile = getResultsFilename( testResult.testFilePath );
			const title = testResult.testResults[ 0 ]?.ancestorTitles[ 0 ];
			const results = JSON.parse(
				readFileSync( resultFile, { encoding: 'UTF-8' } )
			);

			console.log( '\n' );
			console.log( 'Results for:', title );
			console.table( results );

			summary += `**${ title }**\n\n`;
			summary += `${ formatAsMarkdownTable( results ) }\n\n`;
		}

		writeFileSync(
			join( __dirname, '/../', '/specs/', 'summary.md' ),
			summary
		);
	}
}

module.exports = PerformanceResultsReporter;
