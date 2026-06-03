const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });

  // Desktop login
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
  const page = await ctx.newPage();
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');

  // 1. Collapsed sidebar
  await page.goto(BASE + '/produtos', { waitUntil: 'networkidle' });
  await page.click('#sidebarToggle');
  await page.waitForTimeout(500);
  await page.screenshot({ path: `${DIR}\\collapsed.png` });
  const col = await page.evaluate(() => {
    const sb = document.querySelector('#sidebar');
    const main = document.querySelector('#mainWrap');
    return { sbW: Math.round(sb.getBoundingClientRect().width), mainML: getComputedStyle(main).marginLeft };
  });
  console.log('COLLAPSED:', JSON.stringify(col));

  // 2. Pagination close-up
  await page.evaluate(() => { const p = document.querySelector('.pagination'); if (p) p.scrollIntoView(); });
  await page.waitForTimeout(300);
  const pagBox = await page.evaluate(() => {
    const p = document.querySelector('.pagination');
    if (!p) return null;
    const r = p.getBoundingClientRect();
    return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) };
  });
  if (pagBox) {
    await page.screenshot({ path: `${DIR}\\pagination.png`, clip: { x: Math.max(0,pagBox.x-10), y: Math.max(0,pagBox.y-10), width: Math.min(800,pagBox.w+20), height: pagBox.h+20 } });
    console.log('PAGINATION box:', JSON.stringify(pagBox));
  }
  await ctx.close();

  // 3. Mobile view
  const mctx = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true });
  const mp = await mctx.newPage();
  await mp.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await mp.fill('input[name=email]', 'admin@admin.com');
  await mp.fill('input[name=password]', 'admin123');
  await mp.click('button[type=submit]');
  await mp.waitForLoadState('networkidle');
  await mp.goto(BASE + '/produtos', { waitUntil: 'networkidle' });
  await mp.waitForTimeout(400);
  await mp.screenshot({ path: `${DIR}\\mobile-produtos.png` });
  const mob = await mp.evaluate(() => ({
    bodyW: document.body.scrollWidth, winW: window.innerWidth,
    sbLeft: Math.round(document.querySelector('#sidebar').getBoundingClientRect().left),
  }));
  console.log('MOBILE:', JSON.stringify(mob), mob.bodyW > mob.winW+5 ? '⚠️ OVERFLOW' : 'ok');

  // 4. Mobile sidebar open
  const menuBtn = await mp.$('button[onclick*="openMobileSidebar"]');
  if (menuBtn) { await menuBtn.click(); await mp.waitForTimeout(400); await mp.screenshot({ path: `${DIR}\\mobile-menu.png` }); console.log('mobile menu opened'); }

  await browser.close();
  console.log('DONE');
})();
