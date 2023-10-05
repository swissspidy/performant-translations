import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import { Scenario, type TestCase } from '../utils/types';

class TestUtils {
	requestUtils: RequestUtils;

	constructor({ requestUtils }: { requestUtils: RequestUtils }) {
		this.requestUtils = requestUtils;
	}

	async prepareTestCase({ scenario, locale }: TestCase) {
		await this.requestUtils.updateSiteSettings({
			language: 'en_US' === locale ? '' : locale,
		});

		if (
			scenario === Scenario.GingerMo ||
			scenario === Scenario.GingerMoPhp
		) {
			await this.requestUtils.activatePlugin('performant-translations');
		}

		if (scenario === Scenario.GingerMo) {
			await this.requestUtils.activatePlugin('ginger-mo-prefer-mo');
		}

		await this.clearCaches();
	}

	// Not using Promise.all() to avoid race conditions.
	async resetSite() {
		await this.requestUtils.updateSiteSettings({
			language: '',
		});

		await this.requestUtils.deactivatePlugin('performant-translations');
		await this.requestUtils.deactivatePlugin('ginger-mo-prefer-mo');

		await this.clearCaches();
	}

	private async clearCaches() {
		await this.requestUtils.request.head(
			`${this.requestUtils.baseURL}/?clear-cache=opcache`
		);
		await this.requestUtils.request.head(
			`${this.requestUtils.baseURL}/?clear-cache=object-cache`
		);
	}
}

export default TestUtils;
