import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

test('shared.css files are byte-identical across all migrated sites', () => {
  const sites = ['hub-site', 'join-site', 'harvest-site', 'give-site'];
  
  // Read the content of shared.css for any site that has it.
  const contents = sites.map(site => {
    const filePath = path.join(__dirname, '..', site, 'shared.css');
    if (fs.existsSync(filePath)) {
      return { site, content: fs.readFileSync(filePath, 'utf-8') };
    }
    return null;
  }).filter(Boolean);

  if (contents.length < 2) {
    // If only one (or zero) site has the shared CSS so far, there's nothing to compare.
    // The test naturally passes, allowing gradual migration.
    return;
  }

  const reference = contents[0];
  for (let i = 1; i < contents.length; i++) {
    expect(contents[i].content, \\/shared.css does not match \/shared.css\).toBe(reference.content);
  }
});
