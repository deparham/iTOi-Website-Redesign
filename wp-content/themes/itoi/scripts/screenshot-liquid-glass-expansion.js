/**
 * Liquid glass expansion verification screenshots — 5 components:
 * nav bar, floating trigger, industry hero cards (2 industries), the
 * platform-demo modal, and an industry tile's "Learn more" pill.
 * Usage: node scripts/screenshot-liquid-glass-expansion.js
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
  const filename = `${idx++}-glassx-${label}.png`;
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

      // 1a. Nav over a photo-heavy section — industry page hero photo.
      await page.goto(new URL('/industries/retail/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => window.scrollTo(0, 140));
      await sleep(400);
      await shot(page, `1a-nav-over-photo-${vp.label}`);

      // 1b. Nav over a plain white/light section — same page, scrolled to
      // the flush white funnel/interaction card area right below the hero
      // photo (also doubles as an early look at Part 3's card).
      await page.evaluate(() => {
        const el = document.getElementById('funnelSection');
        if (el) {
          window.scrollTo(0, el.getBoundingClientRect().bottom + window.scrollY - 300);
        }
      });
      await sleep(400);
      await shot(page, `1b-nav-over-white-${vp.label}`);

      // 2. Floating trigger button, glass state — homepage, scrolled so
      // it's visible over page content (not the very top).
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => window.scrollTo(0, 400));
      await sleep(400);
      await shot(page, `2-trigger-glass-${vp.label}`);

      // 3a/3b. Industry hero cards — Retail + a second industry
      // (Banking & Finance) to confirm consistency across different photos.
      await page.goto(new URL('/industries/retail/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(400);
      await shot(page, `3a-industry-card-retail-${vp.label}`, false);

      await page.goto(new URL('/industries/banking-finance/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(400);
      await shot(page, `3b-industry-card-banking-${vp.label}`, false);

      // 4. Platform-demo modal open, real dashboard data.
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.evaluate(() => {
        const btn = document.getElementById('platformDemoPlayBtn') || document.getElementById('platformDemoLearnMoreBtn');
        if (btn) btn.click();
      });
      await page.waitForSelector('#platformDemoOverlay.open', { timeout: 5000 });
      await sleep(400);
      await shot(page, `4-platform-demo-modal-${vp.label}`, false);

      // 5. Industry tile "Learn more" pill glass state — /industries/ archive.
      await page.goto(new URL('/industries/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(400);
      await shot(page, `5-industry-tile-pill-${vp.label}`, false);

      await page.close();
    }
    console.log('\nDone.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Liquid glass expansion screenshot run failed:', err);
  process.exit(1);
});
