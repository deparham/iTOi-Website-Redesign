const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const SITE_URL = 'http://192.168.22.80';
const OUT_DIR = path.join('/var/www/html/wp-content/themes/itoi', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

const VIEWPORTS = [
  { width: 375, height: 900, label: '375' },
  { width: 1440, height: 1000, label: '1440' },
];

let shotIndex = 300;

async function shoot(page, label) {
  shotIndex++;
  const filename = `${shotIndex}-${label}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: true });
  console.log(`  saved ${filename}`);
}

async function forEachViewport(browser, url, fn) {
  for (const vp of VIEWPORTS) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.width, height: vp.height });
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 30000 });
    await fn(page, vp.label);
    await page.close();
  }
}

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    console.log('Guides index (default, 3 industries):');
    await forEachViewport(browser, `${SITE_URL}/education/guides/`, async (page, vp) => {
      await shoot(page, `edu-guides-3ind-default-${vp}`);
    });

    console.log('Guides index (filtered to Banking & Finance):');
    await forEachViewport(browser, `${SITE_URL}/education/guides/`, async (page, vp) => {
      const total = await page.$$eval('.guide-card', (els) => els.length);
      const pill = await page.$('[data-filter="industry-23492"]'); // Banking & Finance
      if (!pill) throw new Error('Banking filter pill not found');
      await pill.click();
      await new Promise((r) => setTimeout(r, 200));
      const visible = await page.$$eval('.guide-card', (els) => els.filter((e) => e.style.display !== 'none').length);
      const visibleTitles = await page.$$eval('.guide-card:not([style*="display: none"]) h2', (els) => els.map((e) => e.textContent.trim()));
      console.log(`    total=${total} visible=${visible} titles=${JSON.stringify(visibleTitles)}`);
      if (visible !== 5) console.log('    WARNING: expected exactly 5 Banking guides');
      await shoot(page, `edu-guides-filtered-banking-${vp}`);
    });

    const guideSlugs = [
      'what-is-facial-recognition-for-branch-security',
      'how-autonomous-security-robots-improve-after-hours-branch-coverage',
      'facial-recognition-watchlists-vs-basic-cctv-monitoring',
      'how-to-reduce-branch-queue-wait-times-with-analytics',
      'branch-security-kpis-every-manager-should-track',
    ];
    for (const slug of guideSlugs) {
      console.log(`Guide detail: ${slug}`);
      await forEachViewport(browser, `${SITE_URL}/education/guides/${slug}/`, async (page, vp) => {
        await shoot(page, `edu-guide-${slug}-${vp}`);
      });
    }

    console.log('\nAll done.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Verification failed:', err);
  process.exit(1);
});
