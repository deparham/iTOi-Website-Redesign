/**
 * Solution Builder walkthrough screenshots — drives the multi-step form,
 * results screen, lead capture, and print proposal end-to-end (the static
 * scripts/screenshot.js can't do this since it only loads one URL; this
 * flow requires real clicks through 7 steps + an AJAX submit).
 * Usage: node scripts/screenshot-solution-builder.js
 */
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');

function nextIndex(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const existing = fs.readdirSync(dir).filter((f) => /^\d+-/.test(f));
  const nums = existing.map((f) => parseInt(f.split('-')[0], 10)).filter((n) => !Number.isNaN(n));
  return nums.length ? Math.max(...nums) + 1 : 1;
}

async function shot(page, idx, label, fullPage) {
  const filename = `${idx}-sb-${label}-1440.png`;
  // fullPage defaults off — a fullPage capture temporarily resizes the
  // viewport to fit the document, and immediately clicking an option right
  // after was observed to intermittently miss the still-settling layout
  // (flaky "not clickable"/timeout errors). Only used where there's no
  // click immediately after (results/proposal shots).
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
  await sleep(200);
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// Puppeteer's coordinate-based .click() (clickablePoint -> mouse move ->
// mouse down/up) was consistently flaky in this sandbox — intermittent
// "not clickable"/timeout failures at a different, unpredictable step each
// run, on elements a manual DOM dump confirmed were genuinely visible and
// active. Dispatching the click directly in-page sidesteps Puppeteer's
// geometry/visibility computation entirely and was reliable across repeats.
async function evalClick(page, selector) {
  await sleep(350);
  const clicked = await page.evaluate((sel) => {
    const el = document.querySelector(sel);
    if (!el) {
      return false;
    }
    el.click();
    return true;
  }, selector);
  if (!clicked) {
    throw new Error(`evalClick: no element matched ${selector}`);
  }
  await sleep(250);
}

async function clickOpt(page, group, value) {
  await evalClick(page, `.sb-options[data-group="${group}"] .sb-opt[data-value="${value}"]`);
}

async function clickNext(page) {
  await evalClick(page, '#sbNext');
}

async function run() {
  let idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
    page.on('console', (m) => console.log('CONSOLE:', m.type(), m.text()));
    // Suppress the sitewide "Find Your Fit" modal's 3.5s auto-open (footer.php/
    // main.js) — this walkthrough easily runs past that dwell time and the
    // modal would otherwise pop up on top of the results screen mid-capture.
    await page.evaluateOnNewDocument(() => {
      window.sessionStorage.setItem('finderAutoShown', '1');
    });

    await page.setViewport({ width: 1440, height: 1000 });
    await page.goto(new URL('/solution-builder/', SITE_URL).toString(), { waitUntil: 'networkidle0' });

    // Step 1 — business type
    await shot(page, idx++, 'step1-business-type');
    await clickOpt(page, 'business_type', 'retail');
    await clickNext(page);

    // Step 2 — employees
    await shot(page, idx++, 'step2-employees');
    await clickOpt(page, 'employees', '11-50');
    await clickNext(page);

    // Step 3 — sites
    await shot(page, idx++, 'step3-sites');
    await clickOpt(page, 'sites', '2-5');
    await clickNext(page);

    // Step 4 — existing CCTV
    await shot(page, idx++, 'step4-cctv');
    await clickOpt(page, 'existing_cctv', 'no');
    await clickNext(page);

    // Step 5 — existing POS
    await shot(page, idx++, 'step5-pos');
    await clickOpt(page, 'existing_pos', 'yes');
    await clickNext(page);

    // Step 6 — cloud-based
    await shot(page, idx++, 'step6-cloud');
    await clickOpt(page, 'cloud_based', 'yes');
    await clickNext(page);

    // Step 7 — challenges (multi-select)
    await shot(page, idx++, 'step7-challenges');
    await clickOpt(page, 'challenges', 'theft');
    await clickOpt(page, 'challenges', 'staffing');
    await clickNext(page);

    // Results screen
    await page.waitForSelector('#sbResults:not([hidden])', { timeout: 15000 });
    await page.waitForFunction(
      () => document.querySelectorAll('#sbRecommendedCards > div').length > 0
    );
    await shot(page, idx++, 'results-recommendations', true);

    // Lead capture — set values directly + dispatch input events, same
    // flakiness-avoidance reasoning as evalClick() above.
    await page.evaluate(
      (name, email, company, phone) => {
        function setVal(id, val) {
          const el = document.getElementById(id);
          el.value = val;
          el.dispatchEvent(new Event('input', { bubbles: true }));
        }
        setVal('sbName', name);
        setVal('sbEmail', email);
        setVal('sbCompany', company);
        setVal('sbPhone', phone);
      },
      'Jordan Reyes',
      'jordan.reyes@example.com',
      'Reyes Retail Group',
      '0400 123 456'
    );
    await shot(page, idx++, 'results-lead-form', true);

    await evalClick(page, '#sbLeadSubmit');
    await page.waitForSelector('#sbDownloadBtn:not(.hidden)', { timeout: 15000 });
    await shot(page, idx++, 'results-lead-success', true);

    // Print proposal — emulate print media, screenshot the rendered layout
    await page.emulateMediaType('print');
    await shot(page, idx++, 'proposal-print', true);
    await page.emulateMediaType('screen');

    // Also grab mobile (375px) of step 1 for the responsive record
    await page.setViewport({ width: 375, height: 812 });
    await page.goto(new URL('/solution-builder/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
    const filename375 = `${idx++}-sb-step1-business-type-375.png`;
    await page.screenshot({ path: path.join(OUT_DIR, filename375), fullPage: true });
    console.log(`-> temporary-screenshots/${filename375}`);

    console.log('Done.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Solution Builder screenshot walkthrough failed:', err);
  process.exit(1);
});
