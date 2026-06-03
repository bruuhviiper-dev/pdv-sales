const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';
(async () => {
  const b = await chromium.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  await p.setViewportSize({ width: 1366, height: 900 });
  await p.goto('http://localhost:8000/login', { waitUntil: 'networkidle' });
  await p.screenshot({ path: `${DIR}\\login-com-logo.png` });
  const c = await p.evaluate(() => ({
    hasLogo: !!document.querySelector('.logo-box img'),
    title: document.querySelector('.login-header h4')?.textContent.trim()
  }));
  console.log(JSON.stringify(c));
  await b.close();
})();
