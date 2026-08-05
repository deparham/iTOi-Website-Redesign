/**
 * Screenshot loop per CLAUDE.md. Usage:
 *   node scripts/screenshot.js <path> [label]
 * Example:
 *   node scripts/screenshot.js / front-page
 *
 * Saves 375px and 1440px PNGs to ./temporary-screenshots/, auto-incremented,
 * never overwritten. No hardcoded machine paths — URL comes from SITE_URL env
 * or defaults to the confirmed local server URL.
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');

const VIEWPORTS = [
  { width: 375, height: 812, label: '375' },
  { width: 1440, height: 900, label: '1440' },
];

function nextIndex(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const existing = fs.readdirSync(dir).filter((f) => /^\d+-/.test(f));
  const nums = existing.map((f) => parseInt(f.split('-')[0], 10)).filter((n) => !Number.isNaN(n));
  return nums.length ? Math.max(...nums) + 1 : 1;
}

async function run() {
  const routePath = process.argv[2] || '/';
  const label = process.argv[3] || routePath.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
  const url = new URL(routePath, SITE_URL).toString();

  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    const idx = nextIndex(OUT_DIR);

    for (const vp of VIEWPORTS) {
      await page.setViewport({ width: vp.width, height: vp.height });
      const response = await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
      const status = response ? response.status() : 'no-response';
      const filename = `${idx}-${label}-${vp.label}.png`;
      const outPath = path.join(OUT_DIR, filename);
      await page.screenshot({ path: outPath, fullPage: true });
      console.log(`[${status}] ${url} @ ${vp.label}px -> temporary-screenshots/${filename}`);
    }
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Screenshot failed:', err);
  process.exit(1);
});
