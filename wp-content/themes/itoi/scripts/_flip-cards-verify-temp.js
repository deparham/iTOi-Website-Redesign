/**
 * Scratch verification script for the "Partners, not vendors" (About page)
 * and homepage solutions-grid flip-card sections. Confirms the flip
 * mechanic actually works (genuine CSS 3D rotateY, not just class presence)
 * at both breakpoints, on both hover (desktop) and tap (touch), per
 * CLAUDE.md's screenshot+verify-loop rule. Not just visual — reads the
 * real computed `transform` on `.flip-card-inner` at each checkpoint.
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

async function getRotation(page, selector) {
  return page.$eval(selector, (el) => {
    const t = getComputedStyle(el).transform;
    return t;
  });
}

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  let idx = nextIndex(OUT_DIR);
  try {
    // ---------- DESKTOP (1440px) ----------
    // Puppeteer/headless Chromium caveat, confirmed via a separate isolated
    // test before writing this: CDP's Emulation domain has no support for
    // forcing the `hover`/`pointer` media features ("Unsupported media
    // feature: hover"), and headless Chromium always reports `(hover: none)`
    // regardless of setViewport's hasTouch/isMobile flags — so our CSS's
    // `@media (hover: hover) { .flip-card:hover ... }` rule cannot be
    // exercised through automated hover in this environment. This is a test
    // harness limitation, not a code bug: a separate probe (unconditional
    // `.flip-card:hover{...}` rule with no media guard, injected via
    // page.addStyleTag, real mouse moved onto the card via page.hover())
    // confirmed the browser DOES apply real :hover state correctly and mid-
    // transitions the transform — proving the underlying mechanism is sound,
    // just gated behind a media feature this sandbox can't simulate. Same
    // class of caveat as the Delivery Model pin section's mobile
    // chrome-hiding note (NOTES.md, 2026-07-23). To depict the flipped state
    // for these screenshots, toggle the SAME shipped `.is-flipped` class
    // touch devices use (not a mock rule) via JS — real shipped CSS, just
    // triggered a different way than a real desktop mouse would.
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900, hasTouch: false, isMobile: false });

      // About page — Partners, not vendors
      await page.goto(new URL('/about/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      const aboutCardSel = '.flip-card';
      await page.waitForSelector(aboutCardSel);
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-about-partners-resting-1440.png`), fullPage: true });
      idx++;

      await page.$eval(aboutCardSel + ':nth-of-type(1)', (el) => el.classList.add('is-flipped'));
      await new Promise((r) => setTimeout(r, 700));
      const hoverRot = await getRotation(page, aboutCardSel + ':nth-of-type(1) .flip-card-inner');
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-about-partners-flipped-1440.png`), fullPage: true });
      console.log('About card 1 .is-flipped transform (1440):', hoverRot);
      idx++;

      // Homepage — solutions grid
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('.flip-card');
      const homeCards = await page.$$('.flip-card');
      // Scroll the solutions grid into view (last set of .flip-card on the page)
      const gridCard = homeCards[homeCards.length - 8]; // first of the 8 solution cards
      await gridCard.evaluate((el) => el.scrollIntoView({ block: 'center' }));
      await new Promise((r) => setTimeout(r, 700));
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-home-solutions-resting-1440.png`), fullPage: false });
      idx++;

      await gridCard.evaluate((el) => el.classList.add('is-flipped'));
      await new Promise((r) => setTimeout(r, 700));
      const homeHoverRot = await gridCard.$eval('.flip-card-inner', (el) => getComputedStyle(el).transform);
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-home-solutions-flipped-1440.png`), fullPage: false });
      console.log('Home solution card .is-flipped transform (1440):', homeHoverRot);
      idx++;

      // Separate isolated proof that real mouse :hover DOES apply correctly
      // in this environment (independent of the hover-media gate) — logged
      // as evidence, not screenshotted (the shipped page has no unconditional
      // hover rule to photograph).
      await page.addStyleTag({ content: '.flip-card:hover .flip-card-inner{transform:rotateY(180deg) !important}' });
      await page.hover(aboutCardSel + ':nth-of-type(1)');
      await new Promise((r) => setTimeout(r, 700));
      const proofRot = await getRotation(page, aboutCardSel + ':nth-of-type(1) .flip-card-inner');
      console.log('PROOF (unconditional :hover rule, real mouse via page.hover(), no media gate) settled transform:', proofRot);

      await page.close();
    }

    // ---------- MOBILE (375px, touch device, no hover) ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 375, height: 812, hasTouch: true, isMobile: true });

      // sessionStorage seed to stop the Find Your Fit modal auto-opening mid-capture
      await page.evaluateOnNewDocument(() => {
        try { sessionStorage.setItem('finderAutoShown', '1'); } catch (e) {}
      });

      await page.goto(new URL('/about/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('.flip-card');
      const hoverNoneMatches = await page.evaluate(() => window.matchMedia('(hover: none)').matches);
      console.log('375px (hasTouch/isMobile) matchMedia(hover:none):', hoverNoneMatches);

      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-about-partners-resting-375.png`), fullPage: true });
      idx++;

      const firstCard = await page.$('.flip-card');
      await firstCard.tap();
      await new Promise((r) => setTimeout(r, 700));
      const isFlipped = await firstCard.evaluate((el) => el.classList.contains('is-flipped'));
      const mobileRot = await firstCard.$eval('.flip-card-inner', (el) => getComputedStyle(el).transform);
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-about-partners-flipped-375.png`), fullPage: true });
      console.log('About card 1 tap -> is-flipped:', isFlipped, 'transform:', mobileRot);
      idx++;

      // tap again -> flips back
      await firstCard.tap();
      await new Promise((r) => setTimeout(r, 700));
      const isFlippedBack = await firstCard.evaluate((el) => el.classList.contains('is-flipped'));
      console.log('About card 1 second tap -> is-flipped (should be false):', isFlippedBack);

      // Homepage grid, mobile
      await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('.flip-card');
      const homeCardsM = await page.$$('.flip-card');
      const gridCardM = homeCardsM[homeCardsM.length - 8];
      await gridCardM.evaluate((el) => el.scrollIntoView({ block: 'center' }));
      await new Promise((r) => setTimeout(r, 700));
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-home-solutions-resting-375.png`), fullPage: false });
      idx++;

      await gridCardM.tap();
      await new Promise((r) => setTimeout(r, 700));
      const homeFlipped = await gridCardM.evaluate((el) => el.classList.contains('is-flipped'));
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-home-solutions-flipped-375.png`), fullPage: false });
      console.log('Home solution card tap -> is-flipped:', homeFlipped);
      idx++;

      await page.close();
    }

    // ---------- REDUCED MOTION check (1440, desktop) ----------
    {
      const page = await browser.newPage();
      await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
      await page.setViewport({ width: 1440, height: 900 });
      await page.goto(new URL('/about/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.waitForSelector('.flip-card');
      const transitionVal = await page.$eval('.flip-card-inner', (el) => getComputedStyle(el).transitionDuration);
      console.log('Reduced-motion .flip-card-inner transition-duration (should be 0s):', transitionVal);
      await page.$eval('.flip-card:nth-of-type(1)', (el) => el.classList.add('is-flipped'));
      await new Promise((r) => setTimeout(r, 50));
      const rmRot = await page.$eval('.flip-card:nth-of-type(1) .flip-card-inner', (el) => getComputedStyle(el).transform);
      console.log('Reduced-motion .is-flipped transform, 50ms after toggle (should already be the settled flipped matrix, since transition-duration is 0s):', rmRot);
      await page.close();
    }

    // ---------- Solution page — static tagline still present ----------
    {
      const page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900 });
      await page.goto(new URL('/solutions/intelligence-analytics/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
      await page.screenshot({ path: path.join(OUT_DIR, `${idx}-solution-tagline-1440.png`), fullPage: false });
      const taglineText = await page.$eval('h1 + p', (el) => el.textContent);
      console.log('Solution page static tagline text:', taglineText);
      idx++;
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
