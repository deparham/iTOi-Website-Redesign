/**
 * Verifies + screenshots the new Solution Builder popup flow (replaces the
 * old Find Your Fit 3-question quiz — see NOTES.md 2026-07-24):
 *  1. Trigger the popup, answer all 7 questions, confirm it hands off to
 *     /solution-builder/ and shows results immediately (no re-asking).
 *  2. Confirm sessionStorage is cleared — reloading that same page shows
 *     the standalone form, not stale results.
 *  3. Separately, a fresh context navigating straight to /solution-builder/
 *     still gets the standalone form (page isn't orphaned for direct/organic
 *     traffic).
 * Usage: node scripts/screenshot-solution-builder-popup.js
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

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function shot(page, idx, label, fullPage) {
  const filename = `${idx}-sbpop-${label}-1440.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
  await sleep(150);
}

async function evalClick(page, selector) {
  await sleep(350);
  const clicked = await page.evaluate((sel) => {
    const el = document.querySelector(sel);
    if (!el) return false;
    el.click();
    return true;
  }, selector);
  if (!clicked) {
    throw new Error(`evalClick: no element matched ${selector}`);
  }
  await sleep(250);
}

async function assertTrue(label, condition) {
  console.log(`${condition ? 'PASS' : 'FAIL'} — ${label}`);
  if (!condition) {
    throw new Error(`Assertion failed: ${label}`);
  }
}

async function run() {
  let idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    // ===================== PART A: popup -> handoff =====================
    const page = await browser.newPage();
    page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
    await page.setViewport({ width: 1440, height: 1000 });
    await page.goto(new URL('/', SITE_URL).toString(), { waitUntil: 'networkidle0' });

    // Open via the floating trigger (one of the two accepted entry points).
    await evalClick(page, '#finderTrigger');
    await page.waitForSelector('#finderOverlay.open', { timeout: 5000 });

    await shot(page, idx++, 'step1-business-type');
    await evalClick(page, '.finder-options[data-group="business_type"] .finder-opt[data-value="retail"]');
    await evalClick(page, '#finderNext');

    await evalClick(page, '.finder-options[data-group="employees"] .finder-opt[data-value="11-50"]');
    await evalClick(page, '#finderNext');

    await evalClick(page, '.finder-options[data-group="sites"] .finder-opt[data-value="6-20"]');
    await evalClick(page, '#finderNext');

    await evalClick(page, '.finder-options[data-group="existing_cctv"] .finder-opt[data-value="no"]');
    await evalClick(page, '#finderNext');

    await evalClick(page, '.finder-options[data-group="existing_pos"] .finder-opt[data-value="yes"]');
    await evalClick(page, '#finderNext');

    await evalClick(page, '.finder-options[data-group="cloud_based"] .finder-opt[data-value="yes"]');
    await evalClick(page, '#finderNext');

    await shot(page, idx++, 'step7-challenges');
    await evalClick(page, '.finder-options[data-group="challenges"] .finder-opt[data-value="theft"]');
    await evalClick(page, '.finder-options[data-group="challenges"] .finder-opt[data-value="outdated-security"]');

    // Finishing step 7 navigates away — wait for the new document instead
    // of another evalClick settle delay.
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 }),
      page.evaluate(() => document.getElementById('finderNext').click()),
    ]);

    const landedUrl = page.url();
    await assertTrue('handoff navigated to /solution-builder/', landedUrl.indexOf('/solution-builder/') !== -1);

    // Results should already be visible — no form, no re-asking.
    await page.waitForSelector('#sbResults:not([hidden])', { timeout: 15000 });
    await page.waitForFunction(() => document.querySelectorAll('#sbRecommendedCards > div').length > 0);
    const formHiddenAfterHandoff = await page.evaluate(() => document.getElementById('sbForm').hidden);
    await assertTrue('standalone form is hidden after popup handoff (not re-asked)', formHiddenAfterHandoff === true);

    await shot(page, idx++, 'handoff-results', true);

    // sessionStorage should already be cleared.
    const storageAfterHandoff = await page.evaluate(() => sessionStorage.getItem('itoi_solution_builder_answers'));
    await assertTrue('sessionStorage answers cleared after use', storageAfterHandoff === null);

    // Reload the same page — should now show the standalone form, not stale results.
    await page.reload({ waitUntil: 'networkidle0' });
    await sleep(300);
    const formVisibleAfterReload = await page.evaluate(() => !document.getElementById('sbForm').hidden);
    const resultsHiddenAfterReload = await page.evaluate(() => document.getElementById('sbResults').hidden);
    await assertTrue('reload shows standalone form (not stale results)', formVisibleAfterReload === true);
    await assertTrue('reload keeps results hidden', resultsHiddenAfterReload === true);
    await shot(page, idx++, 'reload-shows-standalone-form', true);

    await page.close();

    // ===================== PART B: direct visit, fresh context =====================
    const context2 = await browser.createBrowserContext();
    const page2 = await context2.newPage();
    page2.on('pageerror', (e) => console.log('PAGEERROR (direct visit):', e.message));
    await page2.setViewport({ width: 1440, height: 1000 });
    await page2.goto(new URL('/solution-builder/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
    await sleep(300);

    const directFormVisible = await page2.evaluate(() => !document.getElementById('sbForm').hidden);
    const directResultsHidden = await page2.evaluate(() => document.getElementById('sbResults').hidden);
    const directStep1Active = await page2.evaluate(() => document.querySelector('.sb-step[data-step="0"]').classList.contains('active'));
    await assertTrue('direct visit (fresh session) shows standalone form', directFormVisible === true);
    await assertTrue('direct visit keeps results hidden', directResultsHidden === true);
    await assertTrue('direct visit starts at step 1', directStep1Active === true);

    await shot(page2, idx++, 'direct-visit-standalone-form', true);

    await context2.close();

    console.log('\nAll checks passed.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Solution Builder popup verification failed:', err);
  process.exit(1);
});
