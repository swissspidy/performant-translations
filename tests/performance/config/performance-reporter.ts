import { join } from 'node:path';
import { writeFileSync } from 'node:fs';
import type {
	FullResult,
	Reporter,
	TestCase,
	TestResult,
} from '@playwright/test/reporter';

class PerformanceReporter implements Reporter {
	allResults: Record<
		string,
		{
			title: string;
			results: Record< string, string | boolean | number >[];
		}
	> = {};

	onTestEnd( test: TestCase, result: TestResult ) {
		const performanceResults = result.attachments.find(
			( attachment ) => attachment.name === 'results'
		);

		if ( performanceResults ) {
			this.allResults[ test.location.file ] ??= {
				// 0 = empty, 1 = browser, 2 = file name.
				title: test.titlePath()[ 3 ],
				results: [],
			};
			this.allResults[ test.location.file ].results.push(
				JSON.parse( performanceResults.body.toString( 'utf-8' ) )
			);
		}
	}

	onEnd( result: FullResult ) {
		const summary = [];

		if ( Object.keys( this.allResults ).length > 0 ) {
			console.log( `\nPerformance Test Results` );
		}

		for ( const [ file, { title, results } ] of Object.entries(
			this.allResults
		) ) {
			console.log( `\n${ title }\n` );
			console.table( results );

			summary.push( {
				file,
				title,
				results,
			} );
		}

		writeFileSync(
			join( process.env.WP_ARTIFACTS_PATH, 'performance-results.json' ),
			JSON.stringify( summary, null, 2 )
		);
	}
}

export default PerformanceReporter;
