const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push(e.message));

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');

  // Go to configuracoes
  await page.goto(BASE + '/configuracoes', { waitUntil: 'networkidle' });
  await page.screenshot({ path: `${DIR}\\config-antes.png` });

  // Fill name and upload logo
  await page.fill('input[name=empresa_nome]', 'Padaria Pão Quente');
  await page.setInputFiles('input[name=empresa_logo]', `${DIR}\\test-logo.png`);
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);
  await page.screenshot({ path: `${DIR}\\config-depois.png` });

  // Check sidebar shows logo + new name
  const check = await page.evaluate(() => {
    const img = document.querySelector('.brand-logo img');
    const name = document.querySelector('.brand-text .name')?.textContent.trim();
    return { sidebarHasLogoImg: !!img, sidebarLogoSrc: img?.src || null, name };
  });
  console.log('SIDEBAR:', JSON.stringify(check));

  // Check login page shows logo
  // logout
  await page.click('button[type=submit][class*=dropdown-item], .dropdown-toggle');
  await page.waitForTimeout(300);
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' }).catch(()=>{});
  // we are still logged in so /login redirects; force logout
  await ctx.clearCookies();
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.screenshot({ path: `${DIR}\\login-com-logo.png` });
  const loginCheck = await page.evaluate(() => {
    const img = document.querySelector('.logo-box img');
    const h4 = document.querySelector('.login-header h4')?.textContent.trim();
    return { loginHasLogo: !!img, title: h4 };
  });
  console.log('LOGIN:', JSON.stringify(loginCheck));

  if (errors.length) { console.log('ERRORS:', errors.join(' | ')); } else console.log('No JS errors');
  await browser.close();
})();
