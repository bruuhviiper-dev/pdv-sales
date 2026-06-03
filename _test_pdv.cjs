const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();
  page.on('console', m => console.log('  [console.' + m.type() + ']', m.text()));
  page.on('pageerror', e => console.log('  [PAGEERROR]', e.message));
  page.on('requestfailed', r => console.log('  [REQ FAILED]', r.url(), r.failure()?.errorText));

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');

  await page.goto(BASE + '/pdv', { waitUntil: 'networkidle' });

  // Is Livewire loaded?
  const lw = await page.evaluate(() => typeof window.Livewire !== 'undefined');
  console.log('Livewire loaded:', lw);

  await page.fill('input[wire\\:model\\.live\\.debounce\\.300ms=busca]', 'arroz');
  // wait for livewire response (the search)
  await page.waitForTimeout(1200);
  const nSug = await page.$$eval('.list-group-item-action', els => els.length);
  console.log('Sugestoes:', nSug);

  if (nSug > 0) {
    // Click and wait for livewire roundtrip
    await Promise.all([
      page.waitForResponse(r => r.url().includes('/livewire/update'), { timeout: 5000 }).catch(()=>console.log('  (sem resposta livewire/update)')),
      page.click('.list-group-item-action'),
    ]);
    await page.waitForTimeout(800);
    const cartCount = await page.evaluate(() => {
      const header = [...document.querySelectorAll('.card-header')].find(h => h.textContent.includes('Carrinho'));
      return header ? header.textContent.trim() : 'NO HEADER';
    });
    console.log('Cart header after click:', cartCount);
  }

  await browser.close();
})();
