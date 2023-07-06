/**
 * External dependencies
 */
import { request } from '@playwright/test';
import type { FullConfig } from '@playwright/test';

/**
 * WordPress dependencies
 */
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

async function globalSetup( config: FullConfig ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	const requestContext = await request.newContext( {
		baseURL,
	} );

	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath,
	} );

	// Authenticate and save the storageState to disk.
	await requestUtils.setupRest();

	await requestContext.dispose();

	await Promise.all( [
		requestUtils.deactivatePlugin( 'ginger-mo' ),
		requestUtils.deactivatePlugin( 'ginger-mo-no-php' ),
		requestUtils.deactivatePlugin( 'sq-lite-object-cache' ),
		requestUtils.deactivatePlugin( 'native-gettext' ),
		requestUtils.deactivatePlugin( 'wp-performance-pack' ),
	] );
}

export default globalSetup;
