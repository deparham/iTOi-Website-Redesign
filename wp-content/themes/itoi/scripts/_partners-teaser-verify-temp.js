/**
 * Verification for the homepage "Partners, not vendors" teaser: renders
 * correctly next to Why Choose ITOI at both breakpoints, and its link
 * navigates to the correct anchored spot on the About page.
 */
const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of [{ w: 1440, h: 900, label: '1440' }, { w: 375, h: 812, label: '375' }]) {
      const page = await browser.newPage();
      await page.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
      await page.setViewport({ width: vp.w, height: vp.h });
      await page.goto(SITE_URL + '/', { waitUntil: 'networkidle0' });

      const teaserText = await page.$eval('a[href*="partners-not-vendors"]', (el) => el.closest('section').textContent.replace(/\s+/g, ' ').trim());
      console.log(`[${vp.label}] Teaser section text:`, teaserText);

      await page.$eval('a[href*="partners-not-vendors"]', (el) => el.scrollIntoView({ block: 'center' }));
      await new Promise((r) => setTimeout(r, 300));
      await page.screenshot({ path: path.join(OUT_DIR, `partners-teaser-${vp.label}.png`) });

      await page.close();
    }

    // Verify the link navigates + scroll target.
    const page2 = await browser.newPage();
    await page2.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
    await page2.setViewport({ width: 1440, height: 900 });
    await page2.goto(SITE_URL + '/', { waitUntil: 'networkidle0' });
    const href = await page2.$eval('a[href*="partners-not-vendors"]', (el) => el.getAttribute('href'));
    console.log('Teaser link href:', href);
    await Promise.all([
      page2.waitForNavigation({ waitUntil: 'load', timeout: 15000 }),
      page2.click('a[href*="partners-not-vendors"]'),
    ]);
    console.log('Navigated to:', page2.url());
    await new Promise((r) => setTimeout(r, 300)); // let scroll-to-anchor settle

    const info = await page2.evaluate(() => {
      const target = document.getElementById('partners-not-vendors');
      if (!target) return { found: false };
      const rect = target.getBoundingClientRect();
      return {
        found: true,
        topWithinViewport: rect.top >= 0 && rect.top < window.innerHeight,
        rectTop: rect.top,
        headingText: target.querySelector('h2') ? target.querySelector('h2').textContent : null,
      };
    });
    console.log('Anchor target info:', info);
    await page2.screenshot({ path: path.join(OUT_DIR, 'partners-teaser-link-destination.png') });

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
