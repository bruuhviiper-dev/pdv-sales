const { chromium } = require('playwright-core');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const SHOT_DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

const pages = process.argv.slice(2);
const targets = pages.length ? pages : [
  ['login', '/login', false],
  ['dashboard', '/dashboard', true],
  ['produtos', '/produtos', true],
  ['pdv', '/pdv', true],
  ['caixa', '/caixa', true],
  ['configuracoes', '/configuracoes', true],
];

(async () => {
  const fs = require('fs');
  if (!fs.existsSync(SHOT_DIR)) fs.mkdirSync(SHOT_DIR, { recursive: true });

  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
  const page = await ctx.newPage();

  const errors = [];
  page.on('console', m => { if (m.type() === 'error') errors.push('CONSOLE: ' + m.text()); });
  page.on('pageerror', e => errors.push('PAGEERROR: ' + e.message));

  // Login first
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');
  console.log('LOGIN OK -> ' + page.url());

  for (const [name, path, auth] of targets) {
    try {
      await page.goto(BASE + path, { waitUntil: 'networkidle', timeout: 15000 });
      await page.waitForTimeout(400);
      await page.screenshot({ path: `${SHOT_DIR}\\${name}.png`, fullPage: false });

      // Layout diagnostics
      const diag = await page.evaluate(() => {
        const sb = document.querySelector('.sidebar, #sidebar');
        const tb = document.querySelector('.topbar');
        const main = document.querySelector('.main-wrap, .main-content');
        const pag = document.querySelector('.pagination');
        const r = el => el ? el.getBoundingClientRect() : null;
        return {
          sidebar: sb ? { top: r(sb).top, left: r(sb).left, width: Math.round(r(sb).width), height: Math.round(r(sb).height) } : null,
          topbar: tb ? { top: r(tb).top, left: Math.round(r(tb).left), width: Math.round(r(tb).width) } : null,
          main: main ? { marginLeft: getComputedStyle(main).marginLeft, top: Math.round(r(main).top) } : null,
          pagination: pag ? { display: getComputedStyle(pag).display, hasPageItem: !!pag.querySelector('.page-item, .page-link'), childTag: pag.firstElementChild ? pag.firstElementChild.tagName : null } : 'NONE',
          bodyScrollW: document.body.scrollWidth,
          winW: window.innerWidth,
        };
      });
      console.log(`\n[${name}] ${path}`);
      console.log('  sidebar:', JSON.stringify(diag.sidebar));
      console.log('  topbar :', JSON.stringify(diag.topbar));
      console.log('  main   :', JSON.stringify(diag.main));
      console.log('  pagin  :', JSON.stringify(diag.pagination));
      if (diag.bodyScrollW > diag.winW + 5) console.log('  ⚠️ HORIZONTAL OVERFLOW: body=' + diag.bodyScrollW + ' win=' + diag.winW);
    } catch (e) {
      console.log(`  ❌ ${name}: ${e.message}`);
    }
  }

  if (errors.length) { console.log('\n=== JS ERRORS ==='); errors.forEach(e => console.log(e)); }
  else console.log('\nNo JS errors.');

  await browser.close();
})();
