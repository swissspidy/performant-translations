import { test, expect } from '../fixtures';

test.describe('Translation Loading', () => {
	test('should correctly translate strings', async ({
		admin,
		page,
		settingsPage,
	}) => {
		await settingsPage.setLocale('de_DE');

		await admin.visitAdminPage('index.php', '');

		expect(
			page.locator('id=dashboard_site_health').getByRole('heading')
		).toContainText('Zustand der Website');
	});
});
