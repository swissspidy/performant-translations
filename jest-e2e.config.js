const config = require( '@wordpress/scripts/config/jest-e2e.config' );

const jestE2eConfig = {
	...config,
	setupFilesAfterEnv: [ '<rootDir>/tests/e2e/config/setup-e2e-tests.js' ],
	reporters: [ [ 'github-actions', { silent: false } ] ],
	testMatch: [ '**/tests/e2e/specs/**/*.[jt]s?(x)' ],
};

module.exports = jestE2eConfig;
