import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const ROUTES = ['/', '/give/', '/harvest/', '/join/'];

test.describe('Accessibility Scans', () => {
  test.beforeEach(async ({ page }) => {
    // Block external assets for faster, flake-free testing
    await page.route('https://fonts.googleapis.com/**', route => route.abort());
    await page.route('https://fonts.gstatic.com/**', route => route.abort());
    await page.route('https://tally.so/**', route => route.abort());
  });

  for (const route of ROUTES) {
    test(\should pass axe-core scan on \\, async ({ page }) => {
      await page.goto(route);
      
      const accessibilityScanResults = await new AxeBuilder({ page }).analyze();
      
      expect(accessibilityScanResults.violations).toEqual([]);
    });
  }
});
