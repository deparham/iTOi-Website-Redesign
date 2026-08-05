/**
 * Liquid glass wave 3 verification screenshots — case study hero cards (3),
 * flip-card capability badges (3 different pages), the disclaimer caption,
 * and the hero progress-dot nav. No RetailNext badge shot — that target
 * doesn't exist anywhere in the codebase, see NOTES.md.
 * Usage: node scripts/screenshot-liquid-glass-wave3.js
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
  const filename = `${idx++}-glassw3-${label}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
}

const CASE_STUDIES = ['drakes-supermarkets', 'brisbane-city-council', 'macquarie-bank'];
const SOLUTIONS = ['intelligence-analytics', 'sensory-intelligence', 'cctv-video-loss-prevention'];

async function run() {
  idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage();
      page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
      await page.evaluateOnNewDocument(() => window.sessionStorage.setItem('finderAutoShown', '1'));
      await page.setViewport({ width: vp.width, height: vp.height });

      // 1/4. Case study hero cards (glass frame + disclaimer badge) — all 3.
      for (const slug of CASE_STUDIES) {
        await page.goto(new URL(`/case-studies/${slug}/`, SITE_URL).toString(), { waitUntil: 'networkidle0' });
        await sleep(350);
        await shot(page, `1-case-study-${slug}-${vp.label}`);
      }

      // Close-up crop of one disclaimer badge for a dedicated legibility check.
      await page.goto(new URL('/case-studies/drakes-supermarkets/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(350);
      const box = await page.evaluate(() => {
        const el = document.querySelector('.disclaimer-glass');
        if (!el) return null;
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, width: r.width, height: r.height };
      });
      if (box) {
        const pad = 10;
        await page.screenshot({
          path: path.join(OUT_DIR, `${idx++}-glassw3-4-disclaimer-closeup-${vp.label}.png`),
          clip: {
            x: Math.max(0, box.x - pad),
            y: Math.max(0, box.y - pad),
            width: Math.min(box.width + pad * 2, vp.width - Math.max(0, box.x - pad)),
            height: Math.min(box.height + pad * 2, vp.height - Math.max(0, box.y - pad)),
          },
          captureBeyondViewport: false,
        });
        console.log(`-> temporary-screenshots/${idx - 1}-glassw3-4-disclaimer-closeup-${vp.label}.png`);
      }

      // 2. Flip-card capability badges — 3 different solution pages.
      for (const slug of SOLUTIONS) {
        await page.goto(new URL(`/solutions/${slug}/`, SITE_URL).toString(), { waitUntil: 'networkidle0' });
        await page.evaluate(() => {
          const el = document.querySelector('.capability-flip-card');
          if (el) el.scrollIntoView({ block: 'center' });
        });
        await sleep(350);
        await shot(page, `2-capability-badges-${slug}-${vp.label}`);
      }

      // 5. Hero progress-dot nav glass state.
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await sleep(500);
      await shot(page, `5-hero-dotnav-glass-${vp.label}`);

      await page.close();
    }
    console.log('\nDone.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Liquid glass wave 3 screenshot run failed:', err);
  process.exit(1);
});
