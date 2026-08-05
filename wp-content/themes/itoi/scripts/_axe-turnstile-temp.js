const puppeteer = require('puppeteer');
const fs = require('fs');
const axeSource = fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');

(async () => {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const page = await browser.newPage();
  await page.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
  await page.goto('http://192.168.22.80/', { waitUntil: 'networkidle0' });
  await page.evaluate(axeSource);
  const results = await page.evaluate(async () => {
    return await axe.run(document.querySelector('#deliveryModel'));
  });
  console.log('Violations:', results.violations.length);
  results.violations.forEach(v => {
    console.log('-', v.id, v.impact, v.description, '(' + v.nodes.length + ' nodes)');
    v.nodes.forEach(n => console.log('   ', n.target, n.failureSummary));
  });
  await browser.close();
})();
