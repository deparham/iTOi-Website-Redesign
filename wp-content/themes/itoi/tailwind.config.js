/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './inc/**/*.php',
    './template-parts/**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Base tokens — PROJECT.md §3
        bg: 'var(--bg)',
        'hero-bg': 'var(--hero-bg)',
        ink: 'var(--ink)',
        line: 'var(--line)',
        text: 'var(--text)',
        'text-muted': 'var(--text-muted)',
        cta: 'var(--cta)',
        'cta-hover': 'var(--cta-hover)',
        teal: {
          900: 'var(--teal-900)',
          800: 'var(--teal-800)',
          700: 'var(--teal-700)',
          500: 'var(--teal-500)',
        },
        // Signature tokens — ITOI's brand navy, the "Live Detection" layer's
        // color. `signature` is for light backgrounds; `signature-bright` is
        // the same hue lightened for dark backgrounds (hero scrim, teal-900
        // sections, dark modal chrome) where plain navy would be dark-on-dark.
        // Never an ordinary CTA/nav color outside the signature layer.
        signature: 'var(--signature)',
        'signature-dim': 'var(--signature-dim)',
        'signature-bright': 'var(--signature-bright)',
        'signature-glow': 'var(--signature-glow)',
      },
      fontFamily: {
        // 2026-08-21 — sitewide swap from Inter to Lora, explicit
        // instruction, confirmed after flagging that it overrides
        // CLAUDE.md/PROJECT.md §3's original "single sans-serif (Inter or
        // Manrope), no serif" rule — both docs updated to match, this
        // isn't an undocumented drift. Key stays named `sans` (not
        // renamed to e.g. `body`) since dozens of templates already
        // reference `font-sans`/the body default via
        // theme('fontFamily.sans') — renaming the key would mean hunting
        // down every one of those for zero functional benefit; the VALUE
        // is what changed. See src/tailwind.css's Lora @font-face block
        // for the actual asset (3 self-hosted weights, no Google Fonts
        // CDN). The short-lived 'trust-serif' single-heading exception
        // this superseded is gone — the whole site is that font now, no
        // separate token needed.
        sans: ['Lora', 'ui-serif', 'Georgia', 'serif'],
        // 2026-08-24 — scoped exception, not a reopening of the decision
        // above. Technology Partners cards (template-parts/partners.php)
        // need real typographic contrast between the section's decorative
        // serif title ("Our Partners", left on the sitewide Lora default)
        // and the cards' own content (eyebrow/name/description/button),
        // which reads as a data label, not prose — explicit instruction.
        // System-UI stack, no new font asset/network request: this theme
        // hasn't shipped a real sans-serif webfont since Inter was removed
        // (see the note above), and adding one back just for one section
        // isn't worth it when every OS already ships a perfectly good
        // sans. Only ever reference this from template-parts/partners.php.
        'ui-sans': ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      },
      // Section-padding scale — 2026-07-27 visual consistency audit (see
      // NOTES.md). 3 tiers only, each a fluid clamp() var (src/tailwind.css
      // :root) so one class (e.g. `py-section-md`) replaces what used to be
      // a one-off arbitrary px value plus a separate min-[...]: breakpoint
      // override per section.
      spacing: {
        'section-sm': 'var(--space-section-sm)',
        'section-md': 'var(--space-section-md)',
        'section-lg': 'var(--space-section-lg)',
      },
    },
  },
  plugins: [],
};
