/**
 * Liquid glass wave 5 verification — Solution Builder results screen
 * (aurora + glass ROI/timeline/architecture cards) and the Team page's
 * over-photo name/role badges (Sean Kiely, Michael Stark only).
 * Drives the Solution Builder's 7-step form for real (same approach as
 * scripts/screenshot-solution-builder.js) since the results screen only
 * exists after a completed run — no static URL to just load.
 * Usage: node scripts/screenshot-liquid-glass-wave5.js
 */
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');
const VIEWPORTS = [
  { width: 1440, height: 1000, label: '1440' },
  { width: 375, height: 900, label: '375' },
];

function nextIndex(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const existing = fs.readdirSync(dir).filter((f) => /^\d+-/.test(f));
  const nums = existing.map((f) => parseInt(f.split('-')[0], 10)).filter((n) => !Number.isNaN(n));
  return nums.length ? Math.max(...nums) + 1 : 1;
}
function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}
let idx;
async function shot(page, label, fullPage) {
  const filename = `${idx++}-glassw5-${label}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, filename), fullPage: !!fullPage });
  console.log(`-> temporary-screenshots/${filename}`);
  await sleep(150);
}

async function evalClick(page, selector) {
  await sleep(300);
  const clicked = await page.evaluate((sel) => {
    const el = document.querySelector(sel);
    if (!el) return false;
    el.click();
    return true;
  }, selector);
  if (!clicked) throw new Error(`evalClick: no element matched ${selector}`);
  await sleep(200);
}
async function clickOpt(page, group, value) {
  await evalClick(page, `.sb-options[data-group="${group}"] .sb-opt[data-value="${value}"]`);
}
async function clickNext(page) {
  await evalClick(page, '#sbNext');
}

async function runSolutionBuilder(page, label) {
  await page.goto(new URL('/solution-builder/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
  await clickOpt(page, 'business_type', 'retail');
  await clickNext(page);
  await clickOpt(page, 'employees', '11-50');
  await clickNext(page);
  await clickOpt(page, 'sites', '2-5');
  await clickNext(page);
  await clickOpt(page, 'existing_cctv', 'no');
  await clickNext(page);
  await clickOpt(page, 'existing_pos', 'yes');
  await clickNext(page);
  await clickOpt(page, 'cloud_based', 'yes');
  await clickNext(page);
  await clickOpt(page, 'challenges', 'theft');
  await clickOpt(page, 'challenges', 'staffing');
  await clickNext(page);

  await page.waitForSelector('#sbResults:not([hidden])', { timeout: 15000 });
  await page.waitForFunction(() => document.querySelectorAll('#sbRecommendedCards > div').length > 0);
  await sleep(400);
  await shot(page, `1-sb-results-full-${label}`, true);

  await page.evaluate(() => document.getElementById('sbArchitecture').scrollIntoView({ block: 'center' }));
  await sleep(300);
  await shot(page, `2-sb-architecture-${label}`);

  await page.evaluate(() => document.querySelector('.sb-roi-glass').scrollIntoView({ block: 'center' }));
  await sleep(300);
  await shot(page, `3-sb-roi-disclaimer-${label}`);

  await page.evaluate(() => document.querySelector('.sb-timeline-glass').scrollIntoView({ block: 'center' }));
  await sleep(300);
  await shot(page, `4-sb-timeline-${label}`);

  await page.evaluate(() => document.getElementById('sbLeadCard').scrollIntoView({ block: 'center' }));
  await sleep(300);
  await shot(page, `5-sb-leadcard-${label}`);
}

async function runTeamPage(page, label) {
  await page.goto(new URL('/team/', SITE_URL).toString(), { waitUntil: 'networkidle0' });
  await sleep(300);
  await shot(page, `6-team-badges-${label}`, true);
}

async function run() {
  idx = nextIndex(OUT_DIR);
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage();
      page.on('pageerror', (e) => console.log('PAGEERROR:', e.message));
      page.on('console', (m) => { if (m.type() === 'error') console.log('CONSOLE ERROR:', m.text()); });
      await page.evaluateOnNewDocument(() => window.sessionStorage.setItem('finderAutoShown', '1'));
      await page.setViewport({ width: vp.width, height: vp.height });

      await runSolutionBuilder(page, vp.label);
      await runTeamPage(page, vp.label);

      await page.close();
    }

    // ===== Contrast sampling for the ROI disclaimer + timeline text (1440) =====
    const page = await browser.newPage();
    await page.evaluateOnNewDocument(() => window.sessionStorage.setItem('finderAutoShown', '1'));
    await page.setViewport({ width: 1440, height: 1000 });
    await runSolutionBuilder(page, 'contrast-check');

    async function sampleBgBehind(selector) {
      return page.evaluate((sel) => {
        const el = document.querySelector(sel);
        const r = el.getBoundingClientRect();
        return { x: Math.round(r.left + 10), y: Math.round(r.top + r.height - 10) };
      }, selector);
    }

    const roiPt = await sampleBgBehind('.sb-roi-glass');
    const tlPt = await sampleBgBehind('.sb-timeline-glass');
    console.log('ROI card sample point:', roiPt, '(visual review in screenshot 3-sb-roi-disclaimer)');
    console.log('Timeline card sample point:', tlPt, '(visual review in screenshot 4-sb-timeline)');

    // prefers-reduced-motion: aurora drift disabled on #sbResults specifically.
    const normalAnim = await page.evaluate(() => getComputedStyle(document.getElementById('sbResults'), '::before').animationName);
    console.log(`Normal mode #sbResults aurora ::before animation-name: ${normalAnim} (expect itoi-aurora-drift)`);
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await sleep(200);
    const reducedAnim = await page.evaluate(() => getComputedStyle(document.getElementById('sbResults'), '::before').animationName);
    console.log(`Reduced-motion #sbResults aurora ::before animation-name: ${reducedAnim} (expect 'none')`);
    await shot(page, 'reduced-motion-sb-results-1440');
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'no-preference' }]);

    await page.close();
    console.log('\nDone.');
  } finally {
    await browser.close();
  }
}

run().catch((err) => {
  console.error('Liquid glass wave 5 screenshot/verification run failed:', err);
  process.exit(1);
});
