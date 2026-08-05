const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

const SLUGS = [
  'intelligence-analytics',
  'customer-engagement-signage',
  'sensory-intelligence',
  'workforce-ops-robotics',
  'cctv-video-loss-prevention',
  'security-access-inventory',
  'back-of-house-integration',
  'it-network-infrastructure',
];

const VIEWPORTS = [
  { width: 375, height: 1400, label: '375' },
  { width: 1440, height: 1400, label: '1440' },
];

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const slug of SLUGS) {
      for (const vp of VIEWPORTS) {
        const page = await browser.newPage();
        await page.setViewport({ width: vp.width, height: vp.height });
        const url = `${SITE_URL}/solutions/${slug}/`;
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
        // clip to hero + intro section only (skip full page)
        const clipHeight = Math.min(vp.height, await page.evaluate(() => document.body.scrollHeight));
        const file = path.join(OUT_DIR, `intro-restructure-${slug}-${vp.label}.png`);
        await page.screenshot({ path: file, clip: { x: 0, y: 0, width: vp.width, height: clipHeight } });
        console.log('Saved', file);
        await page.close();
      }
    }
  } finally {
    await browser.close();
  }
}

run().catch((e) => { console.error(e); process.exit(1); });
