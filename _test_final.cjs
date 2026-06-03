const { chromium } = require('playwright-core');
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8000';
const DIR = 'C:\\Users\\Particular\\Desktop\\templatemonster\\_shots';

(async () => {
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push('PAGEERR: ' + e.message));
  page.on('console', m => { if (m.type()==='error' && !m.text().includes('favicon')) errors.push('CONSOLE: ' + m.text()); });

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[name=email]', 'admin@admin.com');
  await page.fill('input[name=password]', 'admin123');
  await page.click('button[type=submit]');
  await page.waitForLoadState('networkidle');

  // ---- PDV: abrir caixa se preciso, fazer uma venda ----
  await page.goto(BASE + '/caixa', { waitUntil: 'networkidle' });
  const precisaAbrir = await page.$('input[name=saldo_abertura]');
  if (precisaAbrir) {
    await page.fill('input[name=saldo_abertura]', '100');
    await page.click('button:has-text("Abrir Caixa")');
    await page.waitForLoadState('networkidle');
    console.log('Caixa aberto');
  } else { console.log('Caixa ja estava aberto'); }

  await page.goto(BASE + '/pdv', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model\\.live\\.debounce\\.300ms=busca]', 'arroz');
  await page.waitForTimeout(900);
  const sugestao = await page.$('.list-group-item-action');
  if (sugestao) {
    await sugestao.click();
    await page.waitForTimeout(700);
    console.log('Produto adicionado ao carrinho');
  } else {
    console.log('⚠️ Nenhuma sugestao apareceu para "arroz"');
  }
  await page.screenshot({ path: `${DIR}\\pdv-carrinho.png` });

  // checar total no carrinho
  const temItem = await page.$('.fw-bold.text-primary');
  console.log('Carrinho tem item com preco:', !!temItem);

  // selecionar dinheiro + valor + finalizar
  const valorPago = await page.$('input[wire\\:model\\.live=valorPago]');
  if (valorPago) {
    await valorPago.fill('100');
    await page.waitForTimeout(500);
  }
  const btnFinalizar = await page.$('button:has-text("Finalizar Venda")');
  if (btnFinalizar) {
    await btnFinalizar.click();
    await page.waitForTimeout(1200);
    const sucesso = await page.$('text=concluída');
    console.log('Venda finalizada:', !!sucesso);
    await page.screenshot({ path: `${DIR}\\pdv-venda-ok.png` });
  }

  // ---- Pagination test ----
  await page.goto(BASE + '/produtos', { waitUntil: 'networkidle' });
  const pag = await page.evaluate(() => {
    const ul = document.querySelector('.pagination');
    if (!ul) return 'NO PAGINATION';
    const r = ul.getBoundingClientRect();
    const links = [...ul.querySelectorAll('.page-link')].map(a => a.textContent.trim() || a.querySelector('i')?.className || '');
    return { visible: r.width > 0 && r.height > 0, w: Math.round(r.width), links: links.slice(0,6) };
  });
  console.log('PAGINATION:', JSON.stringify(pag));
  // screenshot pagination footer
  const cf = await page.$('.card-footer');
  if (cf) await cf.screenshot({ path: `${DIR}\\pagination-final.png` });

  console.log(errors.length ? '\nERRORS:\n' + errors.join('\n') : '\n✅ Sem erros JS');
  await browser.close();
})();
