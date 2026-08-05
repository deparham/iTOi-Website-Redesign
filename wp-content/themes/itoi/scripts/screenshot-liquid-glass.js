/**
 * Liquid glass test verification screenshots — CONTAINED test, two
 * components only (mega-hero CTAs, Solution Builder popup card).
 * Usage: node scripts/screenshot-liquid-glass.js
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
  const filename = `${idx++}-glass-${label}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
}

// Forces the exact fallback declarations from the @supports block directly
// (higher-specificity inline override), since this headless Chromium build
// keeps reporting CSS.supports('backdrop-filter', ...) === true regardless
// of --disable-features=BackdropFilter/CSSBackdropFilter/kBackdropFilter
// (all three tried) — true browser-capability toggling wasn't achievable
// here. This still faithfully renders what the fallback rule PRODUCES
// (same background values, no blur), just not via real feature detection.
// See NOTES.md for the full note on this limitation.
const FORCE_FALLBACK_CSS = `
  .hero-cta-primary, .hero-cta-secondary {
    background: rgba(14,17,22,0.85) !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
  }
  .finder-card {
    background: rgba(255,255,255,0.97) !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
  }
`;

async function run() {
  idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage();
      page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
      // Suppress the popup's 3.5s auto-open so "hero at rest" is clean.
      await page.evaluateOnNewDocument(() => {
        window.sessionStorage.setItem('finderAutoShown', '1');
      });
      await page.setViewport({ width: vp.width, height: vp.height });

      // 1. Hero at rest — both CTAs, animated background visible.
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(400);
      await shot(page, `1-hero-at-rest-${vp.label}`);

      // 2. Hero — "Get demo" hover state.
      await page.hover('.hero-cta-primary');
      await sleep(300);
      await shot(page, `2-hero-get-demo-hover-${vp.label}`);

      // 3. Popup modal open, real form content.
      await page.evaluate(() => document.getElementById('finderTrigger').click());
      await page.waitForSelector('#finderOverlay.open', { timeout: 5000 });
      await sleep(400);
      await shot(page, `3-modal-glass-${vp.label}`, true);

      // 4a. Fallback — hero (modal closed, force-fallback CSS injected).
      await page.evaluate(() => {
        document.getElementById('finderOverlay').classList.remove('open');
        document.body.style.overflow = '';
      });
      await page.addStyleTag({ content: FORCE_FALLBACK_CSS });
      await sleep(300);
      await shot(page, `4a-fallback-hero-${vp.label}`);

      // 4b. Fallback — modal (same forced CSS still applied).
      await page.evaluate(() => document.getElementById('finderTrigger').click());
      await page.waitForSelector('#finderOverlay.open', { timeout: 5000 });
      await sleep(300);
      await shot(page, `4b-fallback-modal-${vp.label}`, true);

      await page.close();
    }
    console.log('\nDone.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Liquid glass screenshot run failed:', err);
  process.exit(1);
});
