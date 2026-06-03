const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const page = await browser.newPage();
  page.on('pageerror', e => console.log('PAGEERROR FULL:\n', e.stack || e.message));
  page.on('console', m => { if (m.type()==='error') console.log('CONSOLE ERR:', m.text()); });

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');
  await page.goto(BASE + '/pdv', { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  // inspect the wire:snapshot / livewire id
  const info = await page.evaluate(() => {
    const el = document.querySelector('[wire\\:id]');
    return {
      hasComponent: !!el,
      wireId: el?.getAttribute('wire:id'),
      scripts: [...document.scripts].map(s => s.src).filter(Boolean).filter(s=>s.includes('livewire')||s.includes('app')),
    };
  });
  console.log('INFO:', JSON.stringify(info, null, 2));

  await page.fill('input[wire\\:model\\.live\\.debounce\\.300ms=busca]', 'arroz');
  await page.waitForTimeout(1500);
  await browser.close();
})();
