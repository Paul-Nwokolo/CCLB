import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  reporter: 'list',
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
  },
  webServer: {
    command: 'npx serve . -p 3000 -c serve.json',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
