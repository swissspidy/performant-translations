import {
	visitAdminPage,
	clearLocalStorage,
	enablePageDialogAccept,
	setBrowserViewport,
	deactivatePlugin,
} from '@wordpress/e2e-test-utils';

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
	enablePageDialogAccept();
	await setupPage();

	await visitAdminPage( 'index.php' );
	await closeFeaturePointers();
	await clearLocalStorage();

	await deactivatePlugin( 'sqlite-object-cache' );
	await deactivatePlugin( 'native-gettext' );
	await deactivatePlugin( 'wp-performance-pack' );
} );

afterEach( async () => {
	await clearLocalStorage();
} );
