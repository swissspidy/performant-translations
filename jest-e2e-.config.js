const config = require( '@wordpress/scripts/config/jest-e2e.config' );

const jestE2EConfig = {
	...config,
	testMatch: [ '**/tests/e2e/specs/**/*.[jt]s?(x)' ],
};

module.exports = jestE2EConfig;
