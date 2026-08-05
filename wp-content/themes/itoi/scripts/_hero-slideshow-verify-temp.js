const puppeteer = require('puppeteer');
const path = require('path');

const SITE_URL = process.env.SITE_URL || 'http://192.168.22.80';
const OUT_DIR = path.join(__dirname, '..', 'temporary-screenshots');

async function heroState(page) {
  return page.evaluate(() => {
    const dots = Array.from(document.querySelectorAll('#dotNav .dot-btn'));
    const heroBox = document.getElementById('megaHero').getBoundingClientRect();
    return {
      dotCount: dots.length,
      activeDotIndex: dots.findIndex((d) => d.classList.contains('active')),
      headlineHTML: document.getElementById('heroHeadline').innerHTML,
      headlineText: document.getElementById('heroHeadline').textContent.trim(),
      subText: document.getElementById('heroSub').textContent.trim(),
      hasPartnerLockup: !!document.querySelector('.hero-partner-lockup'),
      heroHeight: Math.round(heroBox.height),
      detectionBoxCount: document.querySelectorAll('#heroBg > div').length,
    };
  });
}

async function run() {
  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    for (const vp of [{ width: 375, height: 812, label: '375' }, { width: 1440, height: 900, label: '1440' }]) {
      const page = await browser.newPage();
      await page.setViewport({ width: vp.width, height: vp.height });
      await page.evaluateOnNewDocument(() => sessionStorage.setItem('finderAutoShown', '1'));
      await page.goto(SITE_URL + '/', { waitUntil: 'networkidle0', timeout: 30000 });

      let s = await heroState(page);
      console.log(`\n[${vp.label}px] ON LOAD: dotCount=${s.dotCount} activeDot=${s.activeDotIndex} height=${s.heroHeight} headline="${s.headlineText}"`);
      await page.screenshot({ path: path.join(OUT_DIR, `hero-slide1-${vp.label}.png`) });

      // Click through each dot explicitly (click-to-jump) rather than
      // waiting for the 5s auto-advance, and confirm each shows distinct content.
      for (let i = 1; i < s.dotCount; i++) {
        await page.click(`#dotNav .dot-btn:nth-child(${i + 1})`);
        await new Promise((r) => setTimeout(r, 150));
        const si = await heroState(page);
        console.log(`[${vp.label}px] clicked dot ${i}: activeDot=${si.activeDotIndex} headline="${si.headlineText}" partnerLockup=${si.hasPartnerLockup}`);
        if (i === s.dotCount - 1) {
          await page.screenshot({ path: path.join(OUT_DIR, `hero-slide${i + 1}-retailnext-${vp.label}.png`) });
        }
      }

      // Confirm ring-fill animation class is applied on the active dot.
      const ringInfo = await page.evaluate(() => {
        const activeDot = document.querySelector('#dotNav .dot-btn.active circle.ring');
        if (!activeDot) return null;
        const cs = getComputedStyle(activeDot);
        return { animationName: cs.animationName, animationDuration: cs.animationDuration };
      });
      console.log(`[${vp.label}px] active dot ring animation:`, JSON.stringify(ringInfo));

      // Confirm auto-advance actually happens (wait past the 5s interval).
      await page.click('#dotNav .dot-btn:nth-child(1)');
      await new Promise((r) => setTimeout(r, 200));
      const beforeAuto = await heroState(page);
      await new Promise((r) => setTimeout(r, 5600));
      const afterAuto = await heroState(page);
      console.log(`[${vp.label}px] auto-advance: before=${beforeAuto.activeDotIndex} after 5.6s=${afterAuto.activeDotIndex}`);

      await page.close();
    }

    // Reduced motion: confirm auto-advance is disabled but dots/slides still render.
    const page = await browser.newPage();
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await page.setViewport({ width: 1440, height: 900 });
    await page.goto(SITE_URL + '/', { waitUntil: 'networkidle0', timeout: 30000 });
    const rmBefore = await heroState(page);
    await new Promise((r) => setTimeout(r, 5600));
    const rmAfter = await heroState(page);
    console.log(`\n[reduced-motion] before=${rmBefore.activeDotIndex} after 5.6s=${rmAfter.activeDotIndex} (should be unchanged — no auto-advance)`);
    await page.close();
  } finally {
    await browser.close();
  }
}

run().catch((e) => { console.error(e); process.exit(1); });
