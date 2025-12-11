const puppeteer = require('puppeteer');
(async () => {
  const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox','--disable-setuid-sandbox'] });
  const page = await browser.newPage();

  const logs = [];
  page.on('console', msg => {
    try { logs.push(msg.text()); } catch(e) { logs.push('console-error'); }
  });
  page.on('pageerror', err => logs.push('PAGE_ERROR: ' + err.message));
  page.on('requestfailed', req => logs.push('REQ_FAILED: ' + req.url() + ' ' + req.failure().errorText));

  const url = 'http://localhost:8000/index.html';
  console.log('Opening', url);
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 20000 });

  // 確認: collapse6 が DOM に現れるか
  try {
    await page.waitForSelector('#collapse6', { timeout: 5000 });
    logs.push('collapse6_present');
  } catch (e) {
    logs.push('collapse6_missing');
  }

  // 期待される流れ: スクリプトがアコーディオンを開き、モーダルが表示される
  try {
    // モーダルに .show が付与されるのを待つ
    await page.waitForSelector('#Modal6-13.show, .modal.show', { timeout: 10000 });
    logs.push('MODAL_SHOWN');
  } catch (e) {
    logs.push('MODAL_NOT_SHOWN: ' + e.message);
  }

  // スクリーンショットを保存
  const shotPath = 'puppeteer_result.png';
  await page.screenshot({ path: shotPath, fullPage: true });
  console.log('Screenshot saved to', shotPath);

  console.log('----Collected Logs----');
  logs.forEach(l => console.log(l));

  await browser.close();
})();
