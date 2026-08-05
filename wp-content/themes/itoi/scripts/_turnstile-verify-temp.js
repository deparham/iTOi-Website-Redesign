/**
 * One-off verification for the Delivery Model turnstile carousel.
 * Checks: at-rest layout, auto-rotation actually advances, clicking a side
 * card / dot jumps immediately, prefers-reduced-motion stays static.
 */
const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

function shot(page, name) {
  return page.screenshot({ path: path.join(OUT_DIR, `turnstile-${name}.png`) });
}

async function centerName(page) {
  return page.$eval('#deliveryViewport .turnstile-card.is-center .turnstile-card-step-name', (el) => el.textContent.trim());
}

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    // Suppress the unrelated "Find Your Fit" auto-popup (fires at 3.5s,
    // z-index 100) so it doesn't cover the carousel mid-test.
    await page.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
    await page.setViewport({ width: 1440, height: 900 });
    await page.goto(SITE_URL + '/', { waitUntil: 'networkidle0' });
    await page.$eval('#deliveryModel', (el) => el.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 400));

    const restName = await centerName(page);
    console.log('At rest, center card:', restName);
    await shot(page, '1-at-rest');

    console.log('Waiting ~5s for auto-rotation...');
    await new Promise((r) => setTimeout(r, 5200));
    const afterName = await centerName(page);
    console.log('After auto-rotation, center card:', afterName);
    await shot(page, '2-after-rotation');
    console.log('ROTATION_CHANGED:', restName !== afterName);

    // Click the NEXT card's visible sliver only (it's absolutely positioned
    // and partly clipped by overflow:hidden — a plain elementHandle.click()
    // targets the full box's center, which can fall in the clipped-away
    // portion; a real visitor can only click the painted sliver, so we
    // compute a point inside the viewport's visible bounds instead).
    const beforeClickName = await centerName(page);
    const viewportBox = await page.$eval('#deliveryViewport', (el) => {
      const r = el.getBoundingClientRect();
      return { x: r.x, y: r.y, width: r.width, height: r.height };
    });
    await page.mouse.click(viewportBox.x + viewportBox.width * 0.95, viewportBox.y + viewportBox.height / 2, { delay: 30 });
    await new Promise((r) => setTimeout(r, 700));
    const afterClickName = await centerName(page);
    console.log('Side-card click: before=', beforeClickName, 'after=', afterClickName);
    await shot(page, '3-after-side-click');
    console.log('SIDE_CLICK_CHANGED:', beforeClickName !== afterClickName);

    // Click dot 4 (index 4, "Manage") to jump directly.
    await page.click('.delivery-progress-dot[data-dot="4"]');
    await new Promise((r) => setTimeout(r, 700));
    const afterDotName = await centerName(page);
    console.log('After clicking dot 4, center card:', afterDotName);
    await shot(page, '4-after-dot-click');

    await page.close();

    // Reduced motion: fresh page/context.
    const page2 = await browser.newPage();
    await page2.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
    await page2.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await page2.setViewport({ width: 1440, height: 900 });
    await page2.goto(SITE_URL + '/', { waitUntil: 'networkidle0' });
    await page2.$eval('#deliveryModel', (el) => el.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 400));
    const rmName1 = await centerName(page2);
    await shot(page2, '5-reduced-motion-t0');
    await new Promise((r) => setTimeout(r, 5200));
    const rmName2 = await centerName(page2);
    await shot(page2, '6-reduced-motion-t5s');
    console.log('Reduced motion center before/after 5s:', rmName1, rmName2, '(should be equal)');
    console.log('REDUCED_MOTION_STATIC:', rmName1 === rmName2);

    // Mobile viewport check.
    const page3 = await browser.newPage();
    await page3.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
    await page3.setViewport({ width: 375, height: 812 });
    await page3.goto(SITE_URL + '/', { waitUntil: 'networkidle0' });
    await page3.$eval('#deliveryModel', (el) => el.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 400));
    await shot(page3, '7-mobile-375');

    await browser.close();
  } catch (err) {
    await browser.close();
    throw err;
  }
}

run().catch((err) => {
  console.error(err);
  process.exit(1);
});
