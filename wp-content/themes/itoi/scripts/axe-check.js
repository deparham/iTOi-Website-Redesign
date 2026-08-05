/**
 * axe-core accessibility check per PROJECT.md §9. Usage:
 *   node scripts/axe-check.js <path> [label]
 * Saves JSON results to ./temporary-axe/, prints a violation summary.
 *
 * For draft/unpublished content (e.g. a case study still in review),
 * set AUTH_COOKIE_NAME/AUTH_COOKIE_VALUE and LOGGED_IN_COOKIE_NAME/
 * LOGGED_IN_COOKIE_VALUE env vars (see wp_generate_auth_cookie()) to
 * view it the same way an editor would in wp-admin's preview.
 */

const puppeteer = require('puppeteer');
const { AxePuppeteer } = require('@axe-core/puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-axe');
const SITE_HOST = new URL(SITE_URL).hostname;

const VIEWPORTS = [
  { width: 375, height: 812, label: '375' },
  { width: 1440, height: 900, label: '1440' },
];

async function run() {
  const routePath = process.argv[2] || '/';
  const label = process.argv[3] || routePath.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
  const url = new URL(routePath, SITE_URL).toString();

  if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    let anyViolations = false;

    if (process.env.AUTH_COOKIE_NAME && process.env.LOGGED_IN_COOKIE_NAME) {
      await page.setCookie(
        { name: process.env.AUTH_COOKIE_NAME, value: process.env.AUTH_COOKIE_VALUE, domain: SITE_HOST, path: '/' },
        { name: process.env.LOGGED_IN_COOKIE_NAME, value: process.env.LOGGED_IN_COOKIE_VALUE, domain: SITE_HOST, path: '/' }
      );
    }

    for (const vp of VIEWPORTS) {
      await page.setViewport({ width: vp.width, height: vp.height });
      await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
      const results = await new AxePuppeteer(page).analyze();

      const outPath = path.join(OUT_DIR, `${label}-${vp.label}.json`);
      fs.writeFileSync(outPath, JSON.stringify(results, null, 2));

      const count = results.violations.length;
      if (count > 0) anyViolations = true;
      console.log(`${label} @ ${vp.label}px: ${count} violation(s)`);
      for (const v of results.violations) {
        console.log(`  [${v.impact}] ${v.id}: ${v.help} (${v.nodes.length} node(s))`);
      }
    }
    process.exitCode = anyViolations ? 1 : 0;
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('axe check failed:', err);
  process.exit(1);
});
