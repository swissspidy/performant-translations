import { join } from 'node:path';
import { writeFileSync } from 'node:fs';
import type {
	FullConfig,
	FullResult,
	Reporter,
	TestCase,
	TestResult,
} from '@playwright/test/reporter';

class PerformanceReporter implements Reporter {
	private shard?: FullConfig[ 'shard' ];

	allResults: Record<
		string,
		{
			title: string;
			results: Record< string, string | boolean | number >[];
		}
	> = {};

	onBegin( config: FullConfig ) {
		if ( config.shard ) {
			this.shard = config.shard;
		}
	}

	onTestEnd( test: TestCase, result: TestResult ) {
		const performanceResults = result.attachments.find(
			( attachment ) => attachment.name === 'results'
		);

		if ( performanceResults?.body ) {
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
			if ( this.shard ) {
				console.log(
					`\nPerformance Test Results ${ this.shard.current }/${ this.shard.total }`
				);
			} else {
				console.log( `\nPerformance Test Results` );
			}
			console.log( `Status: ${ result.status }` );
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
			join(
				process.env.WP_ARTIFACTS_PATH as string,
				this.shard
					? `performance-results-${ this.shard.current }-${ this.shard.total }.json`
					: 'performance-results.json'
			),
			JSON.stringify( summary, null, 2 )
		);
	}
}

export default PerformanceReporter;
