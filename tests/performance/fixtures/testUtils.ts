import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import { Scenario, type TestCase } from '../utils/types';

class TestUtils {
	requestUtils: RequestUtils;

	constructor( { requestUtils }: { requestUtils: RequestUtils } ) {
		this.requestUtils = requestUtils;
	}

	async prepareTestCase( {
		objectCache,
		scenario,
	}: Omit< TestCase, 'locale' > ) {
		if ( objectCache ) {
			await this.requestUtils.activatePlugin( 'sq-lite-object-cache' );
		}

		if ( scenario === Scenario.Dynamo ) {
			await this.requestUtils.activatePlugin( 'dyna-mo' );
		}

		if ( scenario === Scenario.NativeGettext ) {
			await this.requestUtils.activatePlugin( 'native-gettext' );
		}

		if ( scenario === Scenario.ObjectCache ) {
			await this.requestUtils.activatePlugin( 'wp-performance-pack' );
		}

		if ( scenario === Scenario.Apcu ) {
			await this.requestUtils.activatePlugin( 'translations-cache' );
		}

		if (
			scenario === Scenario.GingerMo ||
			scenario === Scenario.GingerMoPhp ||
			scenario === Scenario.GingerMoJson
		) {
			await this.requestUtils.activatePlugin( 'ginger-mo' );
		}

		if ( scenario === Scenario.GingerMo ) {
			await this.requestUtils.activatePlugin( 'ginger-mo-prefer-mo' );
		}

		if ( scenario === Scenario.GingerMoJson ) {
			await this.requestUtils.activatePlugin( 'ginger-mo-prefer-json' );
		}
	}

	// Not using Promise.all() to avoid race conditions.
	async teardown() {
		await this.requestUtils.deactivatePlugin( 'dyna-mo' );
		await this.requestUtils.deactivatePlugin( 'ginger-mo' );
		await this.requestUtils.deactivatePlugin( 'ginger-mo-prefer-json' );
		await this.requestUtils.deactivatePlugin( 'ginger-mo-prefer-mo' );
		await this.requestUtils.deactivatePlugin( 'sq-lite-object-cache' );
		await this.requestUtils.deactivatePlugin( 'native-gettext' );
		await this.requestUtils.deactivatePlugin( 'wp-performance-pack' );
		await this.requestUtils.deactivatePlugin( 'translations-cache' );
	}
}

export default TestUtils;
