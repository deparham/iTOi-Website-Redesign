/**
 * Scratch verification script for the Industries section rebuild:
 * (1) tile sizing standardized (equal widths across all 7 tiles),
 * (2) flip-card mechanic reused (photo front / summary back),
 * (3) touch-specific two-tap-then-navigate click-through,
 * (4) desktop click-through (once "flipped"), and
 * (5) the arrow-nav carousel still scrolls independently of the flip.
 * Also confirms the bracket/tag artifact is gone from this section's
 * heading and spot-checks a few other pages for the same fix.
 *
 * Headless-Chromium caveat (same one already documented in
 * _flip-cards-verify-temp.js): CDP cannot force the `hover` media
 * feature, so matchMedia('(hover: none)') always reports true here
 * regardless of viewport/touch flags. To exercise the desktop
 * click-through branch specifically, this script overrides
 * window.matchMedia via evaluateOnNewDocument before main.js runs, so
 * initFlipCards() picks up isTouch=false — a legitimate way to reach
 * that code path in this harness, not a mock of the feature itself.
 */
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

function nextIndex(dir) {
  const existing = fs.readdirSync(dir).filter((f) => /^\d+-/.test(f));
  const nums = existing.map((f) => parseInt(f.split('-')[0], 10)).filter((n) => !Number.isNaN(n));
  return nums.length ? Math.max(...nums) + 1 : 1;
}

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  let idx = nextIndex(OUT_DIR);
  try {
    // ---------- DESKTOP (1440px) ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900, hasTouch: false, isMobile: false });
      await page.evaluateOnNewDocument(() => {
        try { sessionStorage.setItem('finderAutoShown', '1'); } catch (e) {}
      });
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('#indCarousel .flip-card');

      // Part 1 — equal tile widths
      const widths = await page.$$eval('#indCarousel .flip-card', (els) => els.map((el) => el.getBoundingClientRect().width));
      console.log('Industries tile widths (1440):', widths);
      const allEqual = widths.every((w) => Math.abs(w - widths[0]) < 0.5);
      console.log('All 7 tiles same width:', allEqual);

      // Part 3 — bracket/tag gone from this heading, fade class still present
      const headingWrap = await page.$('section:has(#indCarousel) .itoi-reveal, div:has(> h2)');
      const revealInfo = await page.evaluate(() => {
        const h2 = Array.from(document.querySelectorAll('h2')).find((h) => h.textContent.trim() === 'Modern protection for any space');
        const wrap = h2 ? h2.closest('.itoi-reveal') : null;
        return {
          hasRevealClass: !!wrap,
          bracketCount: wrap ? wrap.querySelectorAll('.itoi-reveal-bracket').length : null,
          tagCount: wrap ? wrap.querySelectorAll('.itoi-reveal-tag').length : null,
        };
      });
      console.log('Industries heading reveal check (expect hasRevealClass true, bracketCount 0, tagCount 0):', revealInfo);

      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-industries-resting-1440.png`), fullPage: false });
      idx++;

      // Flip via real CSS hover is not forceable headlessly (documented
      // limitation) — visually confirm the flipped BACK FACE content
      // (summary text) via the shipped .is-flipped class, same technique
      // as the existing flip-card verify script.
      const firstCard = await page.$('#indCarousel .flip-card');
      await firstCard.evaluate((el) => el.classList.add('is-flipped'));
      await new Promise((r) => setTimeout(r, 700));
      const backText = await firstCard.$eval('.flip-card-back', (el) => el.textContent.trim());
      const rot = await firstCard.$eval('.flip-card-inner', (el) => getComputedStyle(el).transform);
      console.log('First industry card back-face text:', backText);
      console.log('First industry card .is-flipped transform:', rot);
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-industries-flipped-1440.png`), fullPage: false });
      idx++;
      await firstCard.evaluate((el) => el.classList.remove('is-flipped'));

      // Carousel arrows still work, independent of flip state
      const before = await page.$eval('#indCarousel', (el) => el.scrollLeft);
      await page.click('#indNext');
      await new Promise((r) => setTimeout(r, 500));
      const after = await page.$eval('#indCarousel', (el) => el.scrollLeft);
      console.log('Carousel scrollLeft before/after clicking #indNext (expect after > before):', before, after);

      await page.close();
    }

    // ---------- DESKTOP click-through (forced isTouch=false path) ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900 });
      await page.evaluateOnNewDocument(() => {
        try { sessionStorage.setItem('finderAutoShown', '1'); } catch (e) {}
        const realMatchMedia = window.matchMedia.bind(window);
        window.matchMedia = function (query) {
          if (query === '(hover: none)') {
            return { matches: false, media: query, addListener() {}, removeListener() {} };
          }
          return realMatchMedia(query);
        };
      });
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('#indCarousel .flip-card');
      const isTouchReported = await page.evaluate(() => window.matchMedia('(hover: none)').matches);
      console.log('Forced desktop matchMedia(hover:none):', isTouchReported, '(expect false)');

      const href = await page.$eval('#indCarousel .flip-card', (el) => el.dataset.href);
      console.log('First industry card data-href:', href);

      // Simulate the flipped-via-hover state a real mouse would have
      // produced, then click the card body (not the inner link) —
      // should navigate per the click-through extension.
      await page.$eval('#indCarousel .flip-card', (el) => el.classList.add('is-flipped'));
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('#indCarousel .flip-card .flip-card-front'),
      ]);
      console.log('Desktop click-through on card body navigated to:', page.url(), '(expect it to match data-href)');
      await page.close();
    }

    // ---------- MOBILE (375px, touch) — two-tap-then-navigate ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 375, height: 812, hasTouch: true, isMobile: true });
      await page.evaluateOnNewDocument(() => {
        try { sessionStorage.setItem('finderAutoShown', '1'); } catch (e) {}
      });
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('#indCarousel .flip-card');

      const widths = await page.$$eval('#indCarousel .flip-card', (els) => els.map((el) => el.getBoundingClientRect().width));
      console.log('Industries tile widths (375):', widths);

      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-industries-resting-375.png`), fullPage: false });
      idx++;

      const firstCard = await page.$('#indCarousel .flip-card');

      // First tap: should flip only, NOT navigate.
      const urlBeforeFirstTap = page.url();
      await firstCard.tap();
      await new Promise((r) => setTimeout(r, 700));
      const isFlipped = await firstCard.evaluate((el) => el.classList.contains('is-flipped'));
      const urlAfterFirstTap = page.url();
      console.log('First tap -> is-flipped (expect true):', isFlipped);
      console.log('URL unchanged after first tap (expect true):', urlBeforeFirstTap === urlAfterFirstTap);
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-industries-flipped-375.png`), fullPage: false });
      idx++;

      // Second tap: should navigate.
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        firstCard.tap(),
      ]);
      console.log('Second tap navigated to (expect industry permalink):', page.url());

      await page.close();
    }

    // ---------- MOBILE — tapping the explicit "Learn more" link navigates directly ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 375, height: 812, hasTouch: true, isMobile: true });
      await page.evaluateOnNewDocument(() => {
        try { sessionStorage.setItem('finderAutoShown', '1'); } catch (e) {}
      });
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('#indCarousel .flip-card');
      const firstCard = await page.$('#indCarousel .flip-card');
      await firstCard.tap(); // reveal the back face first
      await new Promise((r) => setTimeout(r, 700));
      const learnMore = await firstCard.$('.flip-card-back a');
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        learnMore.tap(),
      ]);
      console.log('Tap on explicit "Learn more" link navigated to:', page.url());
      await page.close();
    }

    // ---------- Site-wide bracket/tag audit (spot check) ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900 });
      const urls = ['/', '/solutions/', '/industries/', '/case-studies/', '/insights/', '/about/'];
      for (const u of urls) {
        await page.goto(new URL(u, SITE_URL).toString(), { waitUntil: 'networkidle0' });
        const counts = await page.evaluate(() => ({
          reveal: document.querySelectorAll('.itoi-reveal').length,
          brackets: document.querySelectorAll('.itoi-reveal-bracket').length,
          tags: document.querySelectorAll('.itoi-reveal-tag').length,
        }));
        console.log(u, counts);
      }
      await page.close();
    }
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Verification failed:', err);
  process.exit(1);
});
