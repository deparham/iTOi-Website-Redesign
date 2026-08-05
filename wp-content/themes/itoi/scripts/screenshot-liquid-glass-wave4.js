/**
 * Liquid glass wave 4 verification — aurora backgrounds + glass cards on
 * the Delivery Model turnstile, Partners-not-vendors, and Why ITOI grid.
 * Covers: screenshots at both breakpoints, prefers-reduced-motion disabling
 * the aurora drift specifically, turnstile auto-rotation + click/dot nav
 * still working, and a scroll-performance smoke test.
 * Usage: node scripts/screenshot-liquid-glass-wave4.js
 */
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
const VIEWPORTS = [
  { width: 1440, height: 900, label: '1440' },
  { width: 375, height: 812, label: '375' },
];

function nextIndex(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const existing = fs.readdirSync(dir).filter((f) => /^\d+-/.test(f));
  const nums = existing.map((f) => parseInt(f.split('-')[0], 10)).filter((n) => !Number.isNaN(n));
  return nums.length ? Math.max(...nums) + 1 : 1;
}
function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}
let idx;
async function shot(page, label, fullPage) {
  const filename = `${idx++}-glassw4-${label}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
}

async function run() {
  idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage();
      page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
      await page.evaluateOnNewDocument(() => window.sessionStorage.setItem('finderAutoShown', '1'));
      await page.setViewport({ width: vp.width, height: vp.height });

      // Delivery Model — aurora + glass turnstile card.
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => document.getElementById('deliveryModel').scrollIntoView({ block: 'center' }));
      await sleep(500);
      await shot(page, `1-delivery-model-${vp.label}`);

      // Partners-not-vendors — aurora + glass flip-card backs (flip one to show the back).
      await page.goto(new URL('/about/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => document.getElementById('partners-not-vendors').scrollIntoView({ block: 'center' }));
      await sleep(400);
      await shot(page, `2-partners-front-${vp.label}`);
      await page.evaluate(() => {
        const card = document.querySelector('#partners-not-vendors .flip-card');
        if (card) card.classList.add('is-flipped');
      });
      await sleep(400);
      await shot(page, `2-partners-back-flipped-${vp.label}`);

      // Why ITOI benefits grid — aurora + glass benefit cards.
      await page.goto(new URL('/industries/retail/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => document.getElementById('why-itoi').scrollIntoView({ block: 'center' }));
      await sleep(500);
      await shot(page, `3-why-itoi-${vp.label}`);

      await page.close();
    }

    // ===================== Interaction + reduced-motion checks (1440 only) =====================
    const page = await browser.newPage();
    page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
    await page.evaluateOnNewDocument(() => window.sessionStorage.setItem('finderAutoShown', '1'));
    await page.setViewport({ width: 1440, height: 900 });
    await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
    await page.evaluate(() => document.getElementById('deliveryModel').scrollIntoView({ block: 'center' }));

    // Turnstile auto-rotation: read the active card's data-index, wait, read again.
    const before = await page.evaluate(() => document.querySelector('.turnstile-card.is-center')?.dataset.index);
    await sleep(6500); // auto-advance interval is a few seconds; give it enough time
    const after = await page.evaluate(() => document.querySelector('.turnstile-card.is-center')?.dataset.index);
    console.log(`Turnstile auto-rotation: index ${before} -> ${after} (${before !== after ? 'PASS, advanced' : 'FAIL, did not advance'})`);

    // Dot navigation: click dot 3, confirm it becomes center.
    await page.evaluate(() => document.querySelector('.delivery-progress-dot[data-dot="3"]').click());
    await sleep(700);
    const afterDotClick = await page.evaluate(() => document.querySelector('.turnstile-card.is-center')?.dataset.index);
    console.log(`Dot-3 click -> center index is now ${afterDotClick} (${afterDotClick === '3' ? 'PASS' : 'FAIL'})`);

    // Arrow navigation.
    await page.evaluate(() => document.getElementById('deliveryNextBtn').click());
    await sleep(700);
    const afterArrowClick = await page.evaluate(() => document.querySelector('.turnstile-card.is-center')?.dataset.index);
    console.log(`Next-arrow click -> center index is now ${afterArrowClick} (expected 4) (${afterArrowClick === '4' ? 'PASS' : 'FAIL'})`);

    // prefers-reduced-motion: aurora drift specifically disabled.
    const normalAnims = await page.evaluate(() => {
      const el = document.querySelector('#deliveryModel.aurora-bg');
      const before = getComputedStyle(el, '::before');
      return before.animationName;
    });
    console.log(`Normal mode aurora ::before animation-name: ${normalAnims} (expect itoi-aurora-drift)`);

    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await page.reload({ waitUntil: 'networkidle0' });
    await page.evaluate(() => document.getElementById('deliveryModel').scrollIntoView({ block: 'center' }));
    await sleep(300);
    const reducedAnims = await page.evaluate(() => {
      const el = document.querySelector('#deliveryModel.aurora-bg');
      const before = getComputedStyle(el, '::before');
      return before.animationName;
    });
    console.log(`Reduced-motion aurora ::before animation-name: ${reducedAnims} (expect 'none')`);
    await shot(page, 'reduced-motion-delivery-model-1440');

    // ===================== Scroll performance smoke test =====================
    // Reset media features, reload without reduced-motion so the drift
    // animation + turnstile timer are both actually running during scroll.
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'no-preference' }]);
    await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
    await sleep(500);
    await page.evaluate(() => document.getElementById('deliveryModel').scrollIntoView({ block: 'start' }));
    await sleep(300);

    const client = await page.target().createCDPSession();
    await client.send('Performance.enable');
    const metricsBefore = await client.send('Performance.getMetrics');
    const startTime = Date.now();

    // Scroll through the page in small steps, sampling frame timing via rAF.
    const frameStats = await page.evaluate(async () => {
      const samples = [];
      let last = performance.now();
      let scrollY = window.scrollY;
      const step = () => new Promise((resolve) => requestAnimationFrame((t) => {
        samples.push(t - last);
        last = t;
        resolve();
      }));
      for (let i = 0; i < 90; i++) {
        window.scrollBy(0, 12);
        await step();
      }
      return samples;
    });
    const elapsed = Date.now() - startTime;
    const avgFrameMs = frameStats.reduce((a, b) => a + b, 0) / frameStats.length;
    const maxFrameMs = Math.max(...frameStats);
    const longFrames = frameStats.filter((f) => f > 33.3).length; // frames slower than ~30fps
    console.log(`Scroll perf smoke test: ${frameStats.length} frames in ${elapsed}ms, avg ${avgFrameMs.toFixed(2)}ms/frame, max ${maxFrameMs.toFixed(2)}ms, ${longFrames} frames slower than 30fps`);

    await page.close();

    console.log('\nDone.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Liquid glass wave 4 screenshot/verification run failed:', err);
  process.exit(1);
});
