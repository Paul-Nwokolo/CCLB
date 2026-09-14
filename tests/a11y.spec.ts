import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const SITES = ['give-site', 'harvest-site', 'hub-site', 'join-site'];

test.describe('Accessibility Scans', () => {
  test.beforeEach(async ({ page }) => {
    // Block external assets for faster, flake-free testing
    await page.route('https://fonts.googleapis.com/**', route => route.abort());
    await page.route('https://fonts.gstatic.com/**', route => route.abort());
    await page.route('https://tally.so/**', route => route.abort());
  });

  for (const site of SITES) {
    test(`should pass axe-core scan on ${site}`, async ({ page }) => {
      await page.goto(`/${site}/index.html`);
      
      const accessibilityScanResults = await new AxeBuilder({ page }).analyze();
      
      expect(accessibilityScanResults.violations).toEqual([]);
    });
  }
});
