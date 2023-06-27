import { rmSync } from 'node:fs';
import { join } from 'node:path';
import {
	visitAdminPage,
	clearLocalStorage,
	enablePageDialogAccept,
	setBrowserViewport,
} from '@wordpress/e2e-test-utils';
import { getResultsFilename } from '../utils';

async function setupPage() {
	await setBrowserViewport( 'large' );
	await page.emulateMediaFeatures( [
		{ name: 'prefers-reduced-motion', value: 'reduce' },
	] );
}

async function closeFeaturePointers() {
	const pointers = await page.$$( '.wp-pointer-buttons .close' );
	for ( const pointer of pointers ) {
		await pointer.click();
	}
}

beforeAll( async () => {
	rmSync( join( __dirname, '/../', '/specs/', 'summary.json' ), {
		force: true,
	} );
	rmSync( getResultsFilename( expect.getState().testPath ), { force: true } );

	enablePageDialogAccept();

	await visitAdminPage( 'index.php' );
	await closeFeaturePointers();
	await clearLocalStorage();
	await setupPage();
} );

afterEach( async () => {
	await clearLocalStorage();
} );
