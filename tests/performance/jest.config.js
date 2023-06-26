const config = require( '@wordpress/scripts/config/jest-e2e.config' );

const jestE2EConfig = {
	...config,
	setupFilesAfterEnv: [ '<rootDir>/config/setup-performance-tests.js' ],
	reporters: [
		...config.reporters,
		'<rootDir>/config/performance-results-reporter.js',
	],
	testMatch: [ '**/tests/performance/specs/**/*.[jt]s?(x)' ],
	globals: {
		// Number of requests to run per test.
		TEST_RUNS: 20,
	},
};

module.exports = jestE2EConfig;
