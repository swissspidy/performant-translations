const config = require( '@wordpress/scripts/config/jest-e2e.config' );

const jestE2eConfig = {
	...config,
	testMatch: [ '**/tests/e2e/specs/**/*.[jt]s?(x)' ],
};

module.exports = jestE2eConfig;
