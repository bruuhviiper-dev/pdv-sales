const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const page = await browser.newPage();
  page.on('response', async r => {
    if (r.url().includes('/livewire/update')) {
      console.log('LIVEWIRE RESP STATUS:', r.status());
      try {
        const body = await r.text();
        console.log('BODY (first 600):', body.slice(0, 600));
      } catch(e) { console.log('cant read body', e.message); }
    }
  });

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');
  await page.goto(BASE + '/pdv', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model\\.live\\.debounce\\.300ms=busca]', 'arroz');
  await page.waitForTimeout(2000);
  await browser.close();
})();
