const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const SITE_HOST = new URL(SITE_URL).hostname;
const OUT_DIR = process.env.AUDIT_OUT_DIR || path.join(__dirname, '..', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

const PAGES = [
  { path: '/', label: 'homepage' },
  { path: '/solutions/workforce-ops-robotics/', label: 'solution' },
  { path: '/industries/retail/', label: 'industry' },
  { path: '/case-studies/drakes-supermarkets/', label: 'case-study' },
  { path: '/solution-builder/', label: 'solution-builder' },
  { path: '/education/', label: 'education-hub' },
  { path: '/about/', label: 'about' },
  { path: '/privacy/', label: 'privacy', auth: true },
  { path: '/team/', label: 'team', auth: true },
];

const VIEWPORTS = [
  { width: 375, height: 900, label: '375' },
  { width: 1440, height: 900, label: '1440' },
];

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const p of PAGES) {
      for (const vp of VIEWPORTS) {
        const page = await browser.newPage();
        if (p.auth && process.env.AUTH_COOKIE_NAME) {
          await page.setCookie(
            { name: process.env.AUTH_COOKIE_NAME, value: process.env.AUTH_COOKIE_VALUE, domain: SITE_HOST, path: '/' },
            { name: process.env.LOGGED_IN_COOKIE_NAME, value: process.env.LOGGED_IN_COOKIE_VALUE, domain: SITE_HOST, path: '/' }
          );
        }
        await page.setViewport({ width: vp.width, height: vp.height });
        const url = new URL(p.path, SITE_URL).toString();
        await page.goto(url, { waitUntil: 'networkidle0', timeout: 45000 });
        await new Promise((r) => setTimeout(r, 300));
        const file = path.join(OUT_DIR, `audit-${p.label}-${vp.label}.png`);
        await page.screenshot({ path: file, fullPage: true });
        console.log('Saved', file);
        await page.close();
      }
    }
  } finally {
    await browser.close();
  }
}

run().catch((e) => { console.error(e); process.exit(1); });
