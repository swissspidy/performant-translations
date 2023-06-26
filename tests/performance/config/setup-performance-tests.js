/**
 * WordPress dependencies
 */
import {
	clearLocalStorage,
	enablePageDialogAccept,
	setBrowserViewport,
	trashAllPosts,
} from '@wordpress/e2e-test-utils';

async function setupPage() {
	await setBrowserViewport( 'large' );
	await page.emulateMediaFeatures( [
		{ name: 'prefers-reduced-motion', value: 'reduce' },
	] );
}

// Before every test suite run, delete all content created by the test. This
// ensures other posts/comments/etc. aren't dirtying tests and tests don't
// depend on each other's side effects.
beforeAll( async () => {
	enablePageDialogAccept();

	await trashAllPosts();
	await clearLocalStorage();
	await setupPage();
} );

afterEach( async () => {
	// Clear localStorage between tests so that the next test starts clean.
	await clearLocalStorage();
	// Close the previous page entirely and create a new page, so that the next
	// test isn't affected by page unload work.
	await page.close();
	page = await browser.newPage();
} );
