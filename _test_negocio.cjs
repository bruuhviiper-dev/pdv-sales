const { chromium } = require('playwright-core');
const fs = require('fs');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

// Gera um PNG de logo via canvas no browser
async function genLogo(page, w, h, bg, text, file) {
  const dataUrl = await page.evaluate(async ({ w, h, bg, text }) => {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const x = c.getContext('2d');
    const grad = x.createLinearGradient(0, 0, w, h);
    grad.addColorStop(0, bg[0]); grad.addColorStop(1, bg[1]);
    x.fillStyle = grad; x.fillRect(0, 0, w, h);
    x.fillStyle = '#fff';
    x.font = `bold ${Math.floor(h * 0.18)}px Arial`;
    x.textAlign = 'center'; x.textBaseline = 'middle';
    x.fillText(text, w / 2, h / 2);
    return c.toDataURL('image/png');
  }, { w, h, bg, text });
  const b64 = dataUrl.split(',')[1];
  fs.writeFileSync(file, Buffer.from(b64, 'base64'));
  return file;
}

(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push('PAGEERR: ' + e.message));

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });

  // gerar imagens de teste
  const sq  = await genLogo(page, 512, 512, ['#10b981', '#059669'], 'MERCADO', `${DIR}\\logo-quadrada.png`);
  const wide = await genLogo(page, 1400, 360, ['#f59e0b', '#d97706'], 'SUPERMERCADO BOA COMPRA', `${DIR}\\logo-larga.png`);
  console.log('Logos geradas:', fs.statSync(sq).size, 'bytes /', fs.statSync(wide).size, 'bytes');

  // login
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');

  async function cadastrar(nome, logo, tag) {
    await page.goto(BASE + '/configuracoes', { waitUntil: 'networkidle' });
    await page.fill('input[name=empresa_nome]', nome);
    await page.fill('input[name=empresa_cnpj]', '45.678.901/0001-23');
    await page.fill('input[name=empresa_telefone]', '(11) 98888-7777');
    await page.fill('input[name=empresa_email]', 'contato@boacompra.com.br');
    await page.fill('input[name=empresa_endereco]', 'Av. Brasil, 1500 - Centro');
    await page.fill('input[name=empresa_cidade]', 'Campinas');
    await page.fill('input[name=empresa_estado]', 'SP');
    await page.setInputFiles('input[name=empresa_logo]', logo);
    await page.click('button[type=submit]:has-text("Salvar")');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(400);

    // medir layout
    const m = await page.evaluate(() => {
      const sb = document.querySelector('#sidebar').getBoundingClientRect();
      const tb = document.querySelector('.topbar').getBoundingClientRect();
      const toggle = document.querySelector('#sidebarToggle').getBoundingClientRect();
      const brandLogo = document.querySelector('.brand-logo');
      const img = brandLogo?.querySelector('img');
      const blRect = brandLogo.getBoundingClientRect();
      return {
        sidebarW: Math.round(sb.width),
        brandLogoW: Math.round(blRect.width), brandLogoH: Math.round(blRect.height),
        imgNatural: img ? `${img.naturalWidth}x${img.naturalHeight}` : 'none',
        imgRendered: img ? `${Math.round(img.getBoundingClientRect().width)}x${Math.round(img.getBoundingClientRect().height)}` : 'none',
        toggleVisible: toggle.width > 0 && toggle.left >= sb.width - 20 && toggle.left <= sb.width,
        toggleLeft: Math.round(toggle.left), toggleTop: Math.round(toggle.top),
        bodyOverflowX: document.body.scrollWidth > window.innerWidth + 5,
      };
    });
    console.log(`\n[${tag}] "${nome}"`);
    console.log('  ', JSON.stringify(m));
    await page.screenshot({ path: `${DIR}\\negocio-${tag}.png` });
    return m;
  }

  await cadastrar('Supermercado Boa Compra', sq, 'quadrada');
  await cadastrar('Supermercado Boa Compra Ltda', wide, 'larga');

  // testar toggle (retrair) com logo
  await page.click('#sidebarToggle');
  await page.waitForTimeout(500);
  await page.screenshot({ path: `${DIR}\\negocio-retraido.png` });
  const col = await page.evaluate(() => {
    const sb = document.querySelector('#sidebar').getBoundingClientRect();
    const tg = document.querySelector('#sidebarToggle').getBoundingClientRect();
    return { sidebarW: Math.round(sb.width), toggleLeft: Math.round(tg.left), toggleClipped: tg.left < 0 };
  });
  console.log('\n[RETRAIDO]', JSON.stringify(col));

  console.log(errors.length ? '\nERROS:\n' + errors.join('\n') : '\n✅ Sem erros JS');
  await browser.close();
})();
