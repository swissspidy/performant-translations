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

	await requestUtils.deactivatePlugin( 'dyna-mo' );
	await requestUtils.deactivatePlugin( 'ginger-mo' );
	await requestUtils.deactivatePlugin( 'ginger-mo-prefer-json' );
	await requestUtils.deactivatePlugin( 'ginger-mo-prefer-mo' );
	await requestUtils.deactivatePlugin( 'sq-lite-object-cache' );
	await requestUtils.deactivatePlugin( 'native-gettext' );
	await requestUtils.deactivatePlugin( 'wp-performance-pack' );
	await requestUtils.deactivatePlugin( 'translations-cache' );

	await requestContext.head(
		`${ requestUtils.baseURL }/?clear-cache=opcache`
	);
	await requestContext.head(
		`${ requestUtils.baseURL }/?clear-cache=object-cache`
	);
	await requestContext.head( `${ requestUtils.baseURL }/?clear-cache=apcu` );

	await requestContext.dispose();
}

export default globalSetup;
